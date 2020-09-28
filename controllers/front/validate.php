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
use PrestaShop\Module\PrestashopCheckout\ValidateOrder;

/**
 * This controller receive ajax call to capture/authorize payment and create a PrestaShop Order
 */
class Ps_CheckoutValidateModuleFrontController extends AbstractApiModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        try {
            $this->checkPrerequisite();

            $cart = $this->context->cart;
            $customer = new Customer($cart->id_customer);

            $bodyValues = $this->getDatasFromRequest();

            if (empty($bodyValues['orderID']) || false === Validate::isGenericName($bodyValues['orderID'])) {
                throw new PsCheckoutException('PayPal Order identifier invalid', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            $this->paypalOrderId = $bodyValues['orderID'];

            $this->module->getLogger()->info(sprintf(
                'ValidateOrder PayPal Order Id : %s Cart : %s',
                $bodyValues['orderID'],
                Validate::isLoadedObject($this->context->cart) ? (int) $this->context->cart->id : 0
            ));

            $currency = $this->context->currency;
            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $accountRepository */
            $accountRepository = $this->module->getService('ps_checkout.repository.paypal.account');
            $merchandId = $accountRepository->getMerchantId();
            $payment = new ValidateOrder($bodyValues['orderID'], $merchandId);

            $dataOrder = [
                'cartId' => (int) $cart->id,
                'amount' => $total,
                'currencyId' => (int) $currency->id,
                'secureKey' => $customer->secure_key,
            ];

            // If the payment is rejected redirect the client to the last checkout step (422 error)
            // API call here
            $response = $payment->validateOrder($dataOrder);

            $this->sendOkResponse(
                [
                    'paypal_status' => $response['status'],
                    'paypal_order' => $response['paypalOrderId'],
                    'paypal_transaction' => $response['transactionIdentifier'],
                    'id_cart' => (int) $this->context->cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order' => (int) $this->module->currentOrder,
                    'secure_key' => $this->context->customer->secure_key,
                ]
            );
        } catch (Exception $exception) {
            $this->handleException($exception);
        }
    }
}
