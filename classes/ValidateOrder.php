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

    /**
     * @var string
     */
    private $paypalOrderId = null;

    /**
     * @var string
     */
    private $merchantId = null;

    /**
     * @param string $paypalOrderId
     * @param string $merchantId
     */
    public function __construct($paypalOrderId, $merchantId)
    {
        $this->setMerchantId($merchantId);
        $this->setPaypalOrderId($paypalOrderId);
    }

    /**
     * Process the validation for an order
     *
     * @param array $payload array with all information required by PaymentModule->validateOrder()
     *
     * @return bool
     */
    public function validateOrder($payload)
    {
        // API call here
        $paypalOrder = new PaypalOrder($this->paypalOrderId);
        $order = $paypalOrder->getOrder();

        if (empty($order)) {
            // @todo quickfix : Call API return nothing or fail
            $message = sprintf('Unable to retrieve Paypal Order for %s', $this->paypalOrderId);
            \PrestaShopLogger::addLog($message, 1, null, null, null, true);
            throw new PsCheckoutException($message);
        }

        $apiOrder = new Order(\Context::getContext()->link);

        switch ($paypalOrder->getOrderIntent()) {
            case self::INTENT_CAPTURE:
                // API call here
                $response = $apiOrder->capture($order['id'], $this->merchantId);
                break;
            case self::INTENT_AUTHORIZE:
                // API call here
                $response = $apiOrder->authorize($order['id'], $this->merchantId);
                break;
            default:
                // @todo quickfix
                $message = sprintf('Unknown Intent type %s, Paypal Order %s', $paypalOrder->getOrderIntent(), $this->paypalOrderId);
                \PrestaShopLogger::addLog($message, 1, null, null, null, true);
                throw new PsCheckoutException($message);
        }

        if (false === $response['status']) {
            // @todo Quickfix
            $message = sprintf('Unable to capture/authorize Paypal Order %s', $this->paypalOrderId);
            \PrestaShopLogger::addLog($message, 1, null, null, null, true);

            return false;
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
                'transaction_id' => $response['body']['id'],
            ],
            $payload['currencyId'],
            false,
            $payload['secureKey']
        );

        if (false === $this->setOrdersMatrice($module->currentOrder, $this->paypalOrderId)) {
            $this->setOrderState($module->currentOrder, self::CAPTURE_STATUS_DECLINED, $payload['paymentMethod']);
            $message = sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $this->paypalOrderId);
            \PrestaShopLogger::addLog($message, 1, null, null, null, true);
            throw new PsCheckoutException($message);
        }

        // TODO : patch the order in order to update the order id with the order id
        // of the prestashop order

        $orderState = $this->setOrderState(
            $module->currentOrder,
            $response['body']['status'],
            $payload['paymentMethod']
        );

        if ($orderState === _PS_OS_PAYMENT_) {
            // @todo this may be useless, previous $module->validateOrder() should save transaction id
            $this->setTransactionId($module->currentOrderReference, $response['body']['id']);
        }

        return true;
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
        $paymentMessage = $module->l('Payment by card');

        if ($paymentMethod === self::PAYMENT_METHOD_PAYPAL) {
            $paymentMessage = $module->l('Payment by PayPal');
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
     */
    private function setOrdersMatrice($orderPrestashopId, $orderPaypalId)
    {
        $orderMatrice = new \OrderMatrice();
        $orderMatrice->id_order_prestashop = $orderPrestashopId;
        $orderMatrice->id_order_paypal = $orderPaypalId;

        return $orderMatrice->add();
    }

    /**
     * Set the transactionId (paypal order id) to the payment associated to the order
     *
     * @param string $psOrderRef from prestashop
     * @param string $transactionId paypal transaction Id
     *
     * @return bool
     */
    private function setTransactionId($psOrderRef, $transactionId)
    {
        $orderPayments = new \PrestaShopCollection('OrderPayment');
        $orderPayments->where('order_reference', '=', $psOrderRef);
        $orderPayment = $orderPayments->getFirst();
        if (true === empty($orderPayment)) {
            return false;
        }
        $payment = new \OrderPayment($orderPayment->id);
        $payment->transaction_id = $transactionId;

        return $payment->save();
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId from prestashop
     * @param string $status
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return string|bool order state id to set to the order depending on the status return by paypal
     */
    private function setOrderState($orderId, $status, $paymentMethod)
    {
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $orderId;

        switch ($status) {
            case self::CAPTURE_STATUS_COMPLETED:
                $orderState = _PS_OS_PAYMENT_;
                break;
            case self::CAPTURE_STATUS_DECLINED:
                $orderState = _PS_OS_ERROR_;
                break;
            case self::CAPTURE_STATUS_PENDING:
                $orderState = $this->getPendingStatusId($paymentMethod);
                break;
            default:
                $orderState = $this->getPendingStatusId($paymentMethod);
                break;
        }

        $orderHistory->changeIdOrderState($orderState, $orderId);
        $orderHistory->addWithemail();

        return $orderState;
    }

    /**
     * Set pending status depending on the method used to make the payment
     *
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return int|bool id state
     */
    private function getPendingStatusId($paymentMethod)
    {
        $stateId = \Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');

        if ($paymentMethod === self::PAYMENT_METHOD_PAYPAL) {
            $stateId = \Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
        }

        return intval($stateId);
    }

    /**
     * setter for merchantId
     *
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * setter for orderId
     *
     * @param string $orderId
     */
    public function setPaypalOrderId($orderId)
    {
        $this->paypalOrderId = $orderId;
    }
}
