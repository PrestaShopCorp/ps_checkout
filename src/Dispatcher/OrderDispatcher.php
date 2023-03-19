<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Dispatcher;

use Module;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use Ps_checkout;
use Psr\Log\LoggerInterface;

class OrderDispatcher implements Dispatcher
{
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PaymentCaptureReversed';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PaymentCaptureRefunded';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PaymentAuthorizationVoided';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PaymentCapturePending';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PaymentCaptureCompleted';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PaymentCaptureDenied';
    const PS_CHECKOUT_ORDER_APPROVED = 'CheckoutOrderApproved';
    const PS_CHECKOUT_ORDER_COMPLETED = 'CheckoutOrderCompleted';
    const PS_CHECKOUT_ORDER_APPROVAL_REVERSED = 'CHECKOUT.PAYMENT-APPROVAL.REVERSED';

    /**
     * @var \PsCheckoutCart
     */
    private $psCheckoutCart;

    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * {@inheritdoc}
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function dispatchEventType($payload)
    {
        if (empty($payload['orderId'])) {
            throw new PsCheckoutException('orderId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        /** @var Ps_checkout $module */
        $module = Module::getInstanceByName('ps_checkout');

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = $module->getService('ps_checkout.event.dispatcher');

        /** @var LoggerInterface $logger */
        $logger = $module->getService('ps_checkout.logger');

        /**
         * @todo check if the webhook received contains more recent data than data we have already on database
         * Due to asynchronous and webhook queue, it can happen webhook received is outdated
         * Compare data using `status` and `update_time`values from $payload['resource']
         * With PsCheckoutCart::$paypal_status and PsCheckoutCart::$date_upd
         */
        switch ($payload['eventType']) {
            case static::PS_CHECKOUT_PAYMENT_COMPLETED:
                $eventDispatcher->dispatch(new PayPalCaptureCompletedEvent($payload['resource']['id'], $payload['orderId']));

                return true;
            case static::PS_CHECKOUT_PAYMENT_PENDING:
                $eventDispatcher->dispatch(new PayPalCapturePendingEvent($payload['resource']['id'], $payload['orderId']));

                return true;
            case static::PS_CHECKOUT_PAYMENT_DENIED:
                $eventDispatcher->dispatch(new PayPalCaptureDeniedEvent($payload['resource']['id'], $payload['orderId']));

                return true;
            case static::PS_CHECKOUT_PAYMENT_REFUNED:
                $eventDispatcher->dispatch(new PayPalCaptureRefundedEvent($payload['resource']['id'], $payload['orderId']));

                return true;
            case static::PS_CHECKOUT_PAYMENT_REVERSED:
                $eventDispatcher->dispatch(new PayPalCaptureReversedEvent($payload['resource']['id'], $payload['orderId']));

                return true;
            case static::PS_CHECKOUT_ORDER_APPROVED:
                $eventDispatcher->dispatch(new PayPalOrderApprovedEvent($payload['orderId']));

                return true;
            case static::PS_CHECKOUT_ORDER_COMPLETED:
                $eventDispatcher->dispatch(new PayPalOrderCompletedEvent($payload['orderId']));

                return true;
            case static::PS_CHECKOUT_ORDER_APPROVAL_REVERSED:
                $eventDispatcher->dispatch(new PayPalOrderApprovalReversedEvent($payload['orderId']));

                return true;
            default:
                $logger->warning(
                    'Unknown webhook, cannot be processed.',
                    [
                        'payload' => $payload,
                    ]
                );

                return true;
        }
    }
}
