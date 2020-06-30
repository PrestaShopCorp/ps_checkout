<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
        }

        if (self::CAPTURE_STATUS_DECLINED === $transactionStatus) {
            throw new PsCheckoutException(sprintf('Transaction declined by PayPal : %s', false === empty($response['body']['details']['description']) ? $response['body']['details']['description'] : 'No detail'), PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
        }

        /** @var \PaymentModule $module */
        $module = \Module::getInstanceByName('ps_checkout');

        $module->validateOrder(
            $payload['cartId'],
            $this->getPendingStatusId($payload['paymentMethod']),
            $payload['amount'],
            $this->getPaymentMessageTranslation($payload['paymentMethod'], $module),
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

        if (false === $this->setOrdersMatrice($module->currentOrder, $this->paypalOrderId)) {
            throw new PsCheckoutException(sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $this->paypalOrderId), PsCheckoutException::PSCHECKOUT_ORDER_MATRICE_ERROR);
        }

        $this->setOrderState(
            $module->currentOrder,
            $transactionStatus,
            $payload['paymentMethod']
        );
    }

    /**
     * Get payment message
     *
     * @param string $paymentMethod can be 'paypal' or 'card'
     * @param \PaymentModule $module
     *
     * @return string translation
     */
    private function getPaymentMessageTranslation($paymentMethod, $module)
    {
        $paymentMessage = $module->l('Payment by PayPal');

        if ($paymentMethod === self::PAYMENT_METHOD_CARD) {
            $paymentMessage = $module->l('Payment by card');
        }

        return $paymentMessage;
    }

    /**
     * Set the matrice order values
     *
     * @param int $orderPrestashopId from prestashop
     * @param string $orderPaypalId paypal order id
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setOrdersMatrice($orderPrestashopId, $orderPaypalId)
    {
        $orderMatrice = new \OrderMatrice();
        $orderMatrice->id_order_prestashop = $orderPrestashopId;
        $orderMatrice->id_order_paypal = $orderPaypalId;

        return $orderMatrice->add();
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId Order identifier
     * @param string $status Capture status
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    private function setOrderState($orderId, $status, $paymentMethod)
    {
        switch ($status) {
            case static::CAPTURE_STATUS_COMPLETED:
                $orderState = $this->getPaidStatusId($orderId);
                break;
            case static::CAPTURE_STATUS_DECLINED:
                $orderState = (int) \Configuration::getGlobalValue('PS_OS_ERROR');
                break;
            default:
                $orderState = $this->getPendingStatusId($paymentMethod);
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
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return int OrderState identifier
     */
    private function getPendingStatusId($paymentMethod)
    {
        if ($paymentMethod === static::PAYMENT_METHOD_CARD) {
            return (int) \Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
        }

        return (int) \Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
    }
}
