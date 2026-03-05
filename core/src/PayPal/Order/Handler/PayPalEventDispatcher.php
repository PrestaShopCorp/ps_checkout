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

namespace PsCheckout\Core\PayPal\Order\Handler;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;

class PayPalEventDispatcher implements PayPalEventDispatcherInterface
{
    private $handlers;

    public function __construct(
        EventHandlerInterface $paymentCompletedHandler,
        EventHandlerInterface $paymentPendingHandler,
        EventHandlerInterface $paymentDeniedHandler,
        EventHandlerInterface $paymentRefundedHandler,
        EventHandlerInterface $paymentReversedHandler,
        EventHandlerInterface $orderApprovedHandler,
        EventHandlerInterface $orderCompletedHandler,
        EventHandlerInterface $orderApprovalReversedHandler
    ) {
        $this->handlers = [
            'PaymentCaptureCompleted' => $paymentCompletedHandler,
            'PaymentCapturePending' => $paymentPendingHandler,
            'PaymentCaptureDenied' => $paymentDeniedHandler,
            'PaymentCaptureRefunded' => $paymentRefundedHandler,
            'PaymentCaptureReversed' => $paymentReversedHandler,
            'CheckoutOrderApproved' => $orderApprovedHandler,
            'CheckoutOrderCompleted' => $orderCompletedHandler,
            'CheckoutPaymentApprovalReversed' => $orderApprovalReversedHandler,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(string $eventName, PayPalOrderResponse $payPalOrderResponse)
    {
        if (isset($this->handlers[$eventName])) {
            $this->handlers[$eventName]->handle($payPalOrderResponse);
        } else {
            throw new PsCheckoutException('Handler not found for event: ' . $eventName);
        }
    }
}
