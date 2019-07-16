<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

namespace PrestaShop\Module\PrestashopCheckout;

class OrderDispatcher
{
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PAYMENT.CAPTURE.REVERSED';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PAYMENT.CAPTURE.REFUNDED';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PAYMENT.CAPTURE.PENDING';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PAYMENT.CAPTURE.DENIED';
    const PS_EVENTTYPE_TO_PS_STATE_ID = array(
        self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED => 6, // Canceled
        self::PS_CHECKOUT_PAYMENT_PENDING => 3, // Processing in progress
        self::PS_CHECKOUT_PAYMENT_COMPLETED => 2, // Payment accepted
        self::PS_CHECKOUT_PAYMENT_DENIED => 8, // Payment error
    );

    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * @param array $payload
     *
     * @return bool
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

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_PENDING
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_COMPLETED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_DENIED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            return $this->dispatchPaymentStatus($payload['eventType'], $psOrderId);
        }

        return true;
    }

    /**
     * Check the PSL orderId value and transform it into a Prestashop OrderId
     *
     * @param int $orderId
     *
     * @return bool|int
     */
    private function getPrestashopOrderId($orderId)
    {
        $orderError = (new WebHookValidation())->validateRefundOrderIdValue($orderId);

        if (!empty($orderError)) {
            throw new UnauthorizedException($orderError);
        }

        $psOrderId = (new PaypalOrderRepository())->getPsOrderIdByPaypalOrderId($orderId);

        if (false === $psOrderId) {
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
     * @param int $orderIdgst
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
