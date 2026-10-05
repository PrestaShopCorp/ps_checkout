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
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\Order\Action\CreateOrderPaymentActionInterface;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Order\Lock\PayPalOrderLockInterface;

class PaymentCompletedEventHandler implements EventHandlerInterface
{
    /**
     * @var CreateOrderActionInterface
     */
    private $createOrderAction;

    /**
     * @var CreateOrderPaymentActionInterface
     */
    private $createOrderPaymentAction;

    /**
     * @var SetOrderStateActionInterface
     */
    private $setCompletedOrderStateAction;

    /**
     * @var PayPalOrderLockInterface
     */
    private $payPalOrderLock;

    public function __construct(
        CreateOrderActionInterface $createOrderAction,
        CreateOrderPaymentActionInterface $createOrderPaymentAction,
        SetOrderStateActionInterface $setCompletedOrderStateAction,
        PayPalOrderLockInterface $payPalOrderLock
    ) {
        $this->createOrderAction = $createOrderAction;
        $this->createOrderPaymentAction = $createOrderPaymentAction;
        $this->setCompletedOrderStateAction = $setCompletedOrderStateAction;
        $this->payPalOrderLock = $payPalOrderLock;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(PayPalOrderResponse $payPalOrderResponse)
    {
        $payPalOrderId = $payPalOrderResponse->getId();

        // This event is handled twice for every capture: synchronously by the front-office
        // request, then again when PayPal delivers the webhook. The idempotency checks of
        // the three actions below only work once the first run has fully completed: while
        // PaymentModule::validateOrder() is still running, the PrestaShop order row exists
        // but has no order details, no payment and no state yet, so a concurrent run would
        // create the invoice before the order details are inserted (empty invoice PDF),
        // add a second order payment and set the order state twice. The lock makes the
        // second request wait for the first one instead.
        $locked = $this->payPalOrderLock->acquire($payPalOrderId);

        try {
            try {
                $this->createOrderAction->execute($payPalOrderResponse);
            } catch (PsCheckoutException $e) {
                if ($e->getCode() !== PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS) {
                    throw $e;
                }
                // Order already exists (duplicate webhook delivery or retry after partial failure).
                // Fall through: createOrderPaymentAction and setCompletedOrderStateAction are both
                // idempotent and must still run to ensure the payment record and order state are set.
            }
            $this->createOrderPaymentAction->execute($payPalOrderResponse);
            $this->setCompletedOrderStateAction->execute($payPalOrderId);
        } finally {
            if ($locked) {
                $this->payPalOrderLock->release($payPalOrderId);
            }
        }
    }
}
