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

use PrestaShop\Module\PrestashopCheckout\Entity\OrderMatrice;

class OrderDispatcher implements Dispatcher
{
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PaymentCaptureReversed';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PaymentCaptureRefunded';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PaymentAuthorizationVoided';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PaymentCapturePending';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PaymentCaptureCompleted';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PaymentCaptureDenied';
    const PS_EVENTTYPE_TO_PS_STATE_ID = [
        self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED => _PS_OS_CANCELED_, // Canceled
        self::PS_CHECKOUT_PAYMENT_PENDING => 3, // Processing in progress
        self::PS_CHECKOUT_PAYMENT_COMPLETED => _PS_OS_PAYMENT_, // Payment accepted
        self::PS_CHECKOUT_PAYMENT_DENIED => _PS_OS_ERROR_, // Payment error
    ];

    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * {@inheritdoc}
     */
    public function dispatchEventType($payload)
    {
        $psOrderId = $this->getPrestashopOrderId($payload['orderId']);

        if (false === $psOrderId) {
            return false;
        }

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REFUNED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            return $this->dispatchPaymentAction($payload['eventType'], $payload['resource'], $psOrderId);
        }

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_COMPLETED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_DENIED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            return $this->dispatchPaymentStatus($payload['eventType'], $psOrderId);
        }

        // For now, if pending, do not change anything
        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_PENDING) {
            return true;
        }

        return true;
    }

    /**
     * Check the PSL orderId value and transform it into a Prestashop OrderId
     *
     * @param string $orderId paypal order id
     *
     * @return bool|int
     */
    private function getPrestashopOrderId($orderId)
    {
        $orderError = (new WebHookValidation())->validateRefundOrderIdValue($orderId);

        if (!empty($orderError)) {
            throw new UnauthorizedException($orderError);
        }

        $psOrderId = (new OrderMatrice())->getOrderPrestashopFromPaypal($orderId);

        if (!$psOrderId) {
            throw new UnprocessableException('order #' . $orderId . ' does not exist');
        }

        return $psOrderId;
    }

    /**
     * Dispatch the Event Type to the payments action Refunded or Revesed
     *
     * @param string $eventType
     * @param array $resource
     * @param int $orderId
     *
     * @return bool
     */
    private function dispatchPaymentAction($eventType, $resource, $orderId)
    {
        $orderError = (new WebHookValidation())->validateRefundResourceValues($resource);

        if (!empty($orderError)) {
            throw new UnauthorizedException($orderError);
        }

        $initiateBy = 'Merchant';

        if ($eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $initiateBy = 'Paypal';
        }

        return (new WebHookOrder($initiateBy, $resource, $orderId))->updateOrder();
    }

    /**
     * Dispatch the event Type the the payment status PENDING / COMPLETED / DENIED / AUTH_VOIDED
     *
     * @param string $eventType
     * @param int $orderId
     *
     * @return bool
     */
    private function dispatchPaymentStatus($eventType, $orderId)
    {
        $orderError = (new WebHookValidation())->validateRefundOrderIdValue($orderId);

        if (!empty($orderError)) {
            throw new UnauthorizedException($orderError);
        }

        $order = new \OrderHistory();
        $order->id_order = $orderId;
        $lastOrderState = $order->getLastOrderState($orderId);

        // Prevent duplicate state entry
        if ((int) self::PS_EVENTTYPE_TO_PS_STATE_ID[$eventType] === $lastOrderState->id) {
            return false;
        }

        $order->changeIdOrderState(
            self::PS_EVENTTYPE_TO_PS_STATE_ID[$eventType],
            $orderId
        );

        if (true !== $order->save()) {
            throw new UnauthorizedException('unable to change the order state');
        }

        return true;
    }
}
