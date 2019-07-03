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
     */
    public function dispatchEventType($payload)
    {
        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REFUNED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $this->dispatchPaymentAction($payload['eventType'], $payload['resource'], $payload['orderId']);
        }

        if ($payload['eventType'] === self::PS_CHECKOUT_PAYMENT_PENDING
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_COMPLETED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_DENIED
        || $payload['eventType'] === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            $this->dispatchPaymentStatus($payload['eventType'], $payload['orderId']);
        }
    }

    /**
     * Dispatch the Event Type to the payments action Refunded or Revesed
     *
     * @param string $eventType
     * @param array $resource
     * @param int $orderId
     */
    private function dispatchPaymentAction($eventType, $resource, $orderId)
    {
        $validationValues = new WebHookValidation();
        $orderError = array_merge(
            $validationValues->validateRefundResourceValues($resource),
            $validationValues->validateRefundOrderIdValue($orderId)
        );

        if (!empty($orderError)) {
            $headerNOCK = new WebHookNock();
            $headerNOCK->returnHeader(401, $orderError);
        }

        $initiateBy = 'Merchant';

        if ($eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $initiateBy = 'Paypal';
        }

        $order = new WebHookOrder($initiateBy, $resource, $orderId);
        $order->updateOrder();
    }

    /**
     * Dispatch the event Type the the payment status PENDING / COMPLETED / DENIED / AUTH_VOIDED
     *
     * @param string $eventType
     * @param int $orderIdgst
     */
    private function dispatchPaymentStatus($eventType, $orderId)
    {
        $validationValues = new WebHookValidation();
        $orderError = $validationValues->validateRefundOrderIdValue($orderId);

        if (!empty($orderError)) {
            $headerNOCK = new WebHookNock();
            $headerNOCK->returnHeader(401, $orderError);
        }

        $paypalOrderRepository = new PaypalOrderRepository();
        $psOrderId = $paypalOrderRepository->getPsOrderIdByPaypalOrderId($orderId);

        if (false === $psOrderId) {
            $headerNOCK = new WebHookNock();
            $headerNOCK->returnHeader(
                422,
                array(
                    'order' => 'order #' . $orderId . ' does not exist',
                )
            );
        }

        $order = new \OrderHistory();
        $order->id_order = $psOrderId;

        $order->changeIdOrderState(
            self::PS_EVENTTYPE_TO_PS_STATE_ID[$eventType],
            $psOrderId
        );

        if (true !== $order->save()) {
            $headerNOCK = new WebHookNock();
            $headerNOCK->returnHeader(
                500,
                array(
                    'fatal' => 'unable to change the order state',
                )
            );
        }
    }
}
