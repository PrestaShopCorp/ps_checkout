<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * This controller receive ajax call to capture/authorize payment and create a PrestaShop Order
 */
class Ps_CheckoutValidateModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        header('content-type:application/json');

        try {
            if (false === $this->checkIfContextIsValid()) {
                throw new PsCheckoutException('The context is not valid', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (false === $this->checkIfPaymentOptionIsAvailable()) {
                throw new PsCheckoutException('This payment method is not available.', PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE);
            }

            $customer = new Customer($this->context->cart->id_customer);

            if (false === Validate::isLoadedObject($customer)) {
                throw new PsCheckoutException('Unable to load Customer', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            if (empty($bodyValues['orderID']) || false === Validate::isGenericName($bodyValues['orderID'])) {
                throw new PsCheckoutException('PayPal Order identifier invalid', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            //@todo fetch PayPal Order, check status in case of retry and update PrestaShop side if needed

            // @todo refactor this
            $apiOrder = new PrestaShop\Module\PrestashopCheckout\Api\Payment\Order(\Context::getContext()->link);

            if ('CAPTURE' === Configuration::get('PS_CHECKOUT_INTENT')) {
                $response = $apiOrder->capture($bodyValues['orderID'], (new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository())->getMerchantId());
            } else {
                $response = $apiOrder->authorize($bodyValues['orderID'], (new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository())->getMerchantId());
            }

            $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
            $psCheckoutCartCollection->where('id_cart', '=', (int) $this->context->cart->id);

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartCollection->getFirst();

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $this->context->cart->id;
                $psCheckoutCart->paypal_intent = 'CAPTURE' === Configuration::get('PS_CHECKOUT_INTENT') ? 'CAPTURE' : 'AUTHORIZE';
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCart->add();
            } else {
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCart->update();
            }

            if ('CAPTURE' === Configuration::get('PS_CHECKOUT_INTENT')) {
                $transaction = false === empty($response['body']['purchase_units'][0]['payments']['captures'][0]['id']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['id'] : '';
            } else {
                $transaction = false === empty($response['body']['purchase_units'][0]['payments']['authorizations'][0]['id']) ? $response['body']['purchase_units'][0]['payments']['authorizations'][0]['id'] : '';
            }

            $this->module->validateOrder(
                (int) $this->context->cart->id,
                (int) $this->getOrderState($psCheckoutCart->paypal_funding),
                (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
                $this->getOptionName($psCheckoutCart->paypal_funding),
                null,
                [
                    'transaction_id' => $transaction,
                ],
                (int) $this->context->currency->id,
                false,
                $customer->secure_key
            );

            //@todo remove cookie
            $this->context->cookie->__unset('ps_checkout_orderId');
            $this->context->cookie->__unset('ps_checkout_fundingSource');

            echo json_encode([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'paypal_status' => $response['body']['status'],
                    'paypal_order' => $response['body']['id'],
                    'paypal_transaction' => $transaction,
                    'id_cart' => (int) $this->context->cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order' => (int) $this->module->currentOrder,
                    'secure_key' => $customer->secure_key,
                ],
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            header('HTTP/1.0 400 Bad Request');

            echo json_encode([
                'status' => false,
                'httpCode' => 400,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * Check if the context is valid
     *
     * @todo Move to main module class
     *
     * @return bool
     */
    private function checkIfContextIsValid()
    {
        return true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @todo Move to main module class
     *
     * @return bool
     */
    private function checkIfPaymentOptionIsAvailable()
    {
        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get OrderState identifier
     *
     * @todo Move to a dedicated Service
     *
     * @param string $fundingSource
     *
     * @return int
     */
    private function getOrderState($fundingSource)
    {
        switch ($fundingSource) {
            case 'card':
                $orderStateId = (int) Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
                break;
            case 'paypal':
                $orderStateId = (int) Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
                break;
            default:
                $orderStateId = (int) Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT');
        }

        return $orderStateId;
    }

    /**
     * Get translated Payment Option name
     *
     * @todo Move to a dedicated Service
     *
     * @param string $fundingSource
     *
     * @return string
     */
    private function getOptionName($fundingSource)
    {
        switch ($fundingSource) {
            case 'card':
                $name = $this->module->l('Payment by card');
                break;
            case 'paypal':
                $name = $this->module->l('Payment by PayPal');
                break;
            default:
                $name = $this->module->displayName;
        }

        return $name;
    }
}
