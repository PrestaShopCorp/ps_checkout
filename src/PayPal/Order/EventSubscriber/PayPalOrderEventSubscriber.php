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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Checkout\CheckoutChecker;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CapturePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventProcessor\PayPalOrderEventProcessor;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PsCheckoutCartRepository $psCheckoutCartRepository,
        private CheckoutChecker $checkoutChecker,
        private CapturePayPalOrderCommandHandler $capturePayPalOrderCommandHandler,
        private PayPalOrderEventProcessor $payPalOrderEventProcessor,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => [
                ['saveCreatedPayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovedEvent::class => [
                ['saveApprovedPayPalOrder'],
                ['updateCache'],
                ['capturePayPalOrder'],
            ],
            PayPalOrderCompletedEvent::class => [
                ['saveCompletedPayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovalReversedEvent::class => [
                ['saveApprovalReversedPayPalOrder'],
                ['setApprovalReversedOrderStatus'],
                ['clearCache'],
            ],
            PayPalOrderUpdatedEvent::class => [
                ['updatePayPalOrder'],
                ['clearCache'],
            ],
        ];
    }

    public function saveCreatedPayPalOrder(PayPalOrderCreatedEvent $event)
    {
        $this->payPalOrderEventProcessor->saveCreatedPayPalOrder($event);
    }

    public function saveApprovedPayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $this->payPalOrderEventProcessor->saveApprovedPayPalOrder($event);
    }

    public function saveCompletedPayPalOrder(PayPalOrderCompletedEvent $event)
    {
        $this->payPalOrderEventProcessor->saveCompletedPayPalOrder($event);
    }

    public function saveApprovalReversedPayPalOrder(PayPalOrderApprovalReversedEvent $event)
    {
        $this->payPalOrderEventProcessor->saveApprovalReversedPayPalOrder($event);
    }

    public function capturePayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        // TODO: Remove this condition when a flag will be added on checkout completed
        if ($psCheckoutCart->isExpressCheckout()) {
            return;
        }

        if (in_array($psCheckoutCart->getPaypalStatus(), [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::CANCELED], true)) {
            return;
        }

        $this->checkoutChecker->continueWithAuthorization($psCheckoutCart->getIdCart(), $event->getOrderPayPal());

        $this->capturePayPalOrderCommandHandler->handle(new CapturePayPalOrderCommand(
            $event->getOrderPayPalId()->getValue(),
            $psCheckoutCart->getPaypalFundingSource()
        ));
    }

    public function setApprovalReversedOrderStatus(PayPalOrderApprovalReversedEvent $event)
    {
        $this->payPalOrderEventProcessor->setApprovalReversedOrderStatus($event);
    }

    public function updatePayPalOrder(PayPalOrderEvent $event)
    {
        $this->payPalOrderEventProcessor->updatePayPalOrder($event);
    }

    public function updateCache(PayPalOrderEvent $event)
    {
        $this->payPalOrderEventProcessor->updateCache($event);
    }

    public function clearCache(PayPalOrderEvent $event)
    {
        $this->payPalOrderEventProcessor->clearCache($event);
    }
}
