<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Entity\OrderMatrice;

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
        $paypalOrder = new PaypalOrder($this->paypalOrderId);
        $order = $paypalOrder->getOrder();
        $apiOrder = new Order(\Context::getContext()->link);

        switch ($paypalOrder->getOrderIntent()) {
            case self::INTENT_CAPTURE:
                $response = $apiOrder->capture($order['id'], $this->merchantId);
                break;
            case self::INTENT_AUTHORIZE:
                $response = $apiOrder->authorize($order['id'], $this->merchantId);
                break;
            default:
                throw new \Exception(sprintf('Unknown Intent type %s', $paypalOrder->getOrderIntent()));
        }

        if (false === $response['status']) {
            return false;
        }

        /** @var \PaymentModule $module */
        $module = \Module::getInstanceByName('ps_checkout');

        $module->validateOrder(
            $payload['cartId'],
            $this->getPendingStatusId($payload['paymentMethod']),
            $payload['amount'],
            $this->getPaymentMessageTranslation($payload['paymentMethod']),
            null,
            $payload['extraVars'],
            $payload['currencyId'],
            false,
            $payload['secureKey']
        );

        if (false === $this->setOrdersMatrice($module->currentOrder, $payload['extraVars']['transaction_id'])) {
            $this->setOrderState($module->currentOrder, self::CAPTURE_STATUS_DECLINED, $payload['paymentMethod']);
            throw new \Exception(sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $payload['extraVars']['transaction_id']));
        }

        // TODO : patch the order in order to update the order id with the order id
        // of the prestashop order

        $orderState = $this->setOrderState(
            $module->currentOrder,
            $response['body']['status'],
            $payload['paymentMethod']
        );

        if ($orderState === _PS_OS_PAYMENT_) {
            $this->setTransactionId($module->currentOrderReference, $response['body']['id']);
        }

        return true;
    }

    /**
     * Get payment message
     *
     * @param string $paymentMethod can be 'paypal' or 'card'
     *
     * @return string translation
     */
    private function getPaymentMessageTranslation($paymentMethod)
    {
        $paymentMessage = 'Payment by card';

        if ($paymentMethod === self::PAYMENT_METHOD_PAYPAL) {
            $paymentMessage = 'Payment by PayPal';
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
        $orderMatrice = new OrderMatrice();
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
        $order = new \OrderHistory();
        $order->id_order = $orderId;

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

        $order->changeIdOrderState($orderState, $orderId);
        $order->save();

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
        $stateId = \Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');

        if ($paymentMethod === self::PAYMENT_METHOD_PAYPAL) {
            $stateId = \Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
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
