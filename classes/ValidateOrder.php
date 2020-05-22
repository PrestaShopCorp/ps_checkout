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
            throw new PsCheckoutException(sprintf('Unable to retrieve Paypal Order for %s', $this->paypalOrderId));
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
                throw new PsCheckoutException(sprintf('Unknown Intent type %s, Paypal Order %s', $paypalOrder->getOrderIntent(), $this->paypalOrderId));
        }

        if (false === $response['status']) {
            return false;
        }

        if ($response['body']['status'] === self::CAPTURE_STATUS_DECLINED) {
            // Avoid order with payment error
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
            throw new PsCheckoutException(sprintf('Set Order Matrice error for Prestashop Order ID : %s and Paypal Order ID : %s', $module->currentOrder, $this->paypalOrderId));
        }

        $this->setOrderState(
            $module->currentOrder,
            $response['body']['status'],
            $payload['paymentMethod']
        );

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
     * Set the status of the prestashop order if the payment has been
     * successfully captured or not
     *
     * @param int $orderId Order identifier
     * @param string $status Capture status
     * @param string $paymentMethod can be 'paypal' or 'card'
     */
    private function setOrderState($orderId, $status, $paymentMethod)
    {
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $orderId;

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

        $orderHistory->changeIdOrderState($orderState, $orderId);
        $orderHistory->addWithemail();
    }

    /**
     * @param int $orderId Order identifier
     *
     * @return int OrderState identifier
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
        if ($paymentMethod === static::PAYMENT_METHOD_PAYPAL) {
            return (int) \Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
        }

        return (int) \Configuration::undefinedMethod('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
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
