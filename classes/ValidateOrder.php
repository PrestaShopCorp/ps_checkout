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

        if ($response['body']['status'] === self::CAPTURE_STATUS_DECLINED) {
            // Avoid order with payment error
            return false;
        }

        /** @var \PaymentModule $module */
        $module = \Module::getInstanceByName('ps_checkout');

        // PaymentModule::validateOrder() can create split order in case out of stock for same Cart and same Payment, all orders have same reference in this case
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

        // process current Order
        $currentOrder = new \Order($module->currentOrder);
        $this->setOrdersMatrice($currentOrder->id, $this->paypalOrderId);
        $this->setOrderState(
            $currentOrder,
            $response['body']['status'],
            $payload['paymentMethod']
        );

        // process current Order children
        /** @var \Order[] $currentOrderChildren */
        $currentOrderChildren = $currentOrder->getBrother();
        foreach ($currentOrderChildren as $order) {
            $this->setOrdersMatrice($order->id, $this->paypalOrderId);
            $this->setOrderState(
                $order,
                $response['body']['status'],
                $payload['paymentMethod']
            );
        }

        // This step is same for all because is based on Order reference
        $this->setTransactionId($module->currentOrder, $response['body']['id']);

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
     * @param int $orderId Order ID from prestashop
     * @param string $transactionId paypal transaction Id
     *
     * @return bool
     */
    private function setTransactionId($orderId, $transactionId)
    {
        $order = new \Order($orderId);
        /** @var \OrderPayment $orderPayment */
        $orderPayment = $order->getOrderPaymentCollection()->getFirst();

        if (true === empty($orderPayment)) {
            // If OrderState used in PaymentModule::validateOrder() is not logable no OrderPayment exist, so it will be created by webhook
            return false;
        }

        if ($orderPayment->transaction_id === $transactionId) {
            // If transaction_id is already saved
            return true;
        }

        $orderPayment->transaction_id = $transactionId;

        return $orderPayment->save();
    }

    /**
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param \Order $order PrestaShop Order
     * @param string $status
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return bool Can return false if mail fail to be sent
     */
    private function setOrderState(\Order $order, $status, $paymentMethod)
    {
        switch ($status) {
            case self::CAPTURE_STATUS_COMPLETED:
                $orderState = _PS_OS_PAYMENT_;
                break;
            case self::CAPTURE_STATUS_DECLINED:
                $orderState = _PS_OS_ERROR_;
                break;
            default:
                $orderState = $this->getPendingStatusId($paymentMethod);
                break;
        }

        if ($order->getCurrentState() == $orderState) {
            return true;
        }

        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $order->id;
        $orderHistory->changeIdOrderState($orderState, $order->id);

        return $orderHistory->addWithemail();
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

        return (int) $stateId;
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
