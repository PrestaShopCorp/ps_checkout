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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * Class that allow to validate an order
 */
class ValidateOrder
{
    const INTENT_CAPTURE = 'CAPTURE';
    const INTENT_AUTHORIZE = 'AUTHORIZE';

    const CAPTURE_STATUS_PENDING = 'PENDING';
    const CAPTURE_STATUS_DENIED = 'DENIED';
    const CAPTURE_STATUS_VOIDED = 'VOIDED';
    const CAPTURE_STATUS_COMPLETED = 'COMPLETED';
    const CAPTURE_STATUS_DECLINED = 'DECLINED';

    const PAYMENT_METHOD_PAYPAL = 'paypal';
    const PAYMENT_METHOD_CARD = 'card';

    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @param string $paypalOrderId
     * @param string $merchantId
     */
    public function __construct($paypalOrderId, $merchantId)
    {
        $this->merchantId = $merchantId;
        $this->paypalOrderId = $paypalOrderId;
    }

    /**
     * Process the validation for an order
     *
     * @param array $payload array with all information required by PaymentModule->validateOrder()
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     */
    public function validateOrder($payload)
    {
        // API call here
        $paypalOrder = new PaypalOrder($this->paypalOrderId);
        $order = $paypalOrder->getOrder();

        if (empty($order)) {
            throw new PsCheckoutException(sprintf('Unable to retrieve Paypal Order for %s', $this->paypalOrderId), PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        $transactionIdentifier = false === empty($order['purchase_units'][0]['payments']['captures'][0]['id']) ? $order['purchase_units'][0]['payments']['captures'][0]['id'] : '';
        $transactionStatus = false === empty($order['purchase_units'][0]['payments']['captures'][0]['status']) ? $order['purchase_units'][0]['payments']['captures'][0]['status'] : '';

        // @todo To be refactored in v2.0.0 with Service Container
        if (true === empty($order['purchase_units'][0]['payments']['captures'])) {
            $apiOrder = new Order(\Context::getContext()->link);
            $response = $apiOrder->capture($order['id'], $this->merchantId); // API call here

            if (false === $response['status']) {
                if (false === empty($response['exceptionMessage']) && false === empty($response['exceptionCode'])) {
                    throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
                }

                if (false === empty($response['body']['message'])) {
                    (new PayPalError($response['body']['message']))->throwException();
                }

                throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
            }

            if (false === empty($response['body']['purchase_units'][0]['payments']['captures'])) {
                $transactionIdentifier = $response['body']['purchase_units'][0]['payments']['captures'][0]['id'];
                $transactionStatus = $response['body']['purchase_units'][0]['payments']['captures'][0]['status'];

                if (self::CAPTURE_STATUS_DECLINED === $transactionStatus
                    && false === empty($response['body']['payment_source'])
                    && false === empty($response['body']['payment_source'][0]['card'])
                    && false === empty($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response'])
                ) {
                    $payPalProcessorResponse = new PayPalProcessorResponse(
                        isset($response['body']['payment_source'][0]['card']['brand']) ? $response['body']['payment_source'][0]['card']['brand'] : null,
                        isset($response['body']['payment_source'][0]['card']['type']) ? $response['body']['payment_source'][0]['card']['type'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code'] : null,
                        isset($response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code']) ? $response['body']['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code'] : null
                    );
                    $payPalProcessorResponse->throwException();
                }
            }

            if (self::CAPTURE_STATUS_DECLINED === $transactionStatus) {
                throw new PsCheckoutException(sprintf('Transaction declined by PayPal : %s', false === empty($response['body']['details']['description']) ? $response['body']['details']['description'] : 'No detail'), PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
            }

            $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
            $psCheckoutCartCollection->where('id_cart', '=', (int) $payload['cartId']);

            /** @var \PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartCollection->getFirst();

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new \PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $payload['cartId'];
                $psCheckoutCart->paypal_intent = $paypalOrder->getOrderIntent();
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCart->add();
            } else {
                $psCheckoutCart->paypal_order = $response['body']['id'];
                $psCheckoutCart->paypal_status = $response['body']['status'];
                $psCheckoutCart->update();
            }

            /** @var \PaymentModule $module */
            $module = \Module::getInstanceByName('ps_checkout');

            $module->validateOrder(
                $payload['cartId'],
                (int) $this->getOrderState($psCheckoutCart->paypal_funding),
                $payload['amount'],
                $this->getOptionName($module, $psCheckoutCart->paypal_funding),
                null,
                [
                    'transaction_id' => $transactionIdentifier,
                ],
                $payload['currencyId'],
                false,
                $payload['secureKey']
            );

            if (empty($module->currentOrder)) {
                throw new PsCheckoutException(sprintf('PrestaShop was unable to returns Prestashop Order ID for Prestashop Cart ID : %s  - Paypal Order ID : %s. This happens when PrestaShop take too long time to create an Order due to heavy processes in hooks actionValidateOrder and/or actionOrderStatusUpdate and/or actionOrderStatusPostUpdate', $payload['cartId'], $this->paypalOrderId), PsCheckoutException::PRESTASHOP_ORDER_ID_MISSING);
            }

            $this->setOrderState(
                $module->currentOrder,
                $transactionStatus,
                $psCheckoutCart->paypal_funding
            );
        }

        return [
            'status' => $response !== null ? $response['body']['status'] : $order['status'],
            'paypalOrderId' => $response !== null ? $response['body']['id'] : $this->paypalOrderId,
            'transactionIdentifier' => $transactionIdentifier,
        ];
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
    private function getOptionName($module, $fundingSource)
    {
        switch ($fundingSource) {
            case 'card':
                $name = $module->l('Payment by card');
                break;
            case 'paypal':
                $name = $module->l('Payment by PayPal');
                break;
            default:
                $name = $module->displayName;
        }

        return $name;
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId Order identifier
     * @param string $status Capture status
     * @param string $paypalFunding can be 'paypal' or 'card'
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setOrderState($orderId, $status, $paypalFunding)
    {
        switch ($status) {
            case static::CAPTURE_STATUS_COMPLETED:
                $orderState = $this->getPaidStatusId($orderId);
                break;
            case static::CAPTURE_STATUS_DECLINED:
                $orderState = (int) \Configuration::getGlobalValue('PS_OS_ERROR');
                break;
            default:
                $orderState = $this->getOrderState($paypalFunding);
                break;
        }

        $order = new \Order($orderId);
        $currentOrderStateId = (int) $order->getCurrentState();

        if ($currentOrderStateId !== $orderState) {
            $orderHistory = new \OrderHistory();
            $orderHistory->id_order = $orderId;
            $orderHistory->changeIdOrderState($orderState, $orderId);
            $orderHistory->addWithemail();
        }
    }

    /**
     * @param int $orderId Order identifier
     *
     * @return int OrderState identifier
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function getPaidStatusId($orderId)
    {
        $order = new \Order($orderId);

        if (\Validate::isLoadedObject($order) && $order->getCurrentState() == \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_UNPAID')) {
            return (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_PAID');
        }

        return (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
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
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
                break;
            case 'paypal':
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
                break;
            default:
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT');
        }

        return $orderStateId;
    }
}
