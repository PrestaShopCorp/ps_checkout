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

class OrderDispatcher implements InterfaceDispatcher
{
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PAYMENT.CAPTURE.REVERSED';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PAYMENT.CAPTURE.REFUNDED';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PAYMENT.CAPTURE.PENDING';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PAYMENT.CAPTURE.DENIED';

    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * @param string $eventType
     * @param array $resource
     */
    public function dispatchEventType($eventType, $resource)
    {
        if ($eventType === self::PS_CHECKOUT_PAYMENT_REFUNED
        || $eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $this->dispatchPaymentAction($eventType, $resource);
        }

        if ($eventType === self::PS_CHECKOUT_PAYMENT_PENDING
        || $eventType === self::PS_CHECKOUT_PAYMENT_COMPLETED
        || $eventType === self::PS_CHECKOUT_PAYMENT_DENIED
        || $eventType === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            $this->dispatchPaymentStatus($eventType, $resource);
        }
    }

    /**
     * Dispatch the Event Type to the payments action Refunded or Revesed
     *
     * @param string $eventType
     * @param array $resource
     */
    private function dispatchPaymentAction($eventType, $resource)
    {
        $initiateBy = 'Merchant';

        if ($eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $initiateBy = 'Paypal';
        }

        $order = new WebHookOrder($initiateBy, $resource);
        $order->updateOrder();
    }

    /**
     * Dispatch the event Type the the payment status PENDING / COMPLETED / DENIED
     *
     * @param string $eventType
     * @param array $resource
     */
    private function dispatchPaymentStatus($eventType, $resource)
    {
        $states = new OrderStates();

        if ($eventType === self::PS_CHECKOUT_PAYMENT_PENDING) {
            // $states->dispatchPaymentStatus($eventType, $resource);
        }

        if ($eventType === self::PS_CHECKOUT_PAYMENT_COMPLETED) {
            // $states->dispatchPaymentStatus($eventType, $resource);
        }

        if ($eventType === self::PS_CHECKOUT_PAYMENT_DENIED) {
            // $states->dispatchPaymentStatus($eventType, $resource);
        }

        if ($eventType === self::PS_CHECKOUT_PAYMENT_AUTH_VOIDED) {
            // $states->dispatchPaymentStatus($eventType, $resource);
        }
    }
}
