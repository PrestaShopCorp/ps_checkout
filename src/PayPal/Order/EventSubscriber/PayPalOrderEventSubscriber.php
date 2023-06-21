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

use Exception;
use PrestaShop\Module\PrestashopCheckout\Checkout\CheckoutChecker;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\SavePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopException;
use Ps_checkout;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @var CheckoutChecker
     */
    private $checkoutChecker;

    /**
     * @var CheckTransitionPayPalOrderStatusService
     */
    private $checkTransitionPayPalOrderStatusService;

    public function __construct(
        Ps_checkout $module,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CacheInterface $orderPayPalCache,
        CheckoutChecker $checkoutChecker,
        CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService
    ) {
        $this->module = $module;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->checkoutChecker = $checkoutChecker;
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
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
                ['updateCache'],
            ],
        ];
    }

    public function saveCreatedPayPalOrder(PayPalOrderCreatedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($psCheckoutCart->getPaypalStatus(), PayPalOrderStatus::CREATED)) {
            return;
        }

        $this->module->getService('ps_checkout.bus.command')->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::CREATED,
            $event->getOrderPayPal()
        ));
    }

    public function saveApprovedPayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($psCheckoutCart->getPaypalStatus(), PayPalOrderStatus::APPROVED)) {
            return;
        }

        $this->module->getService('ps_checkout.bus.command')->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::APPROVED,
            $event->getOrderPayPal()
        ));
    }

    public function saveCompletedPayPalOrder(PayPalOrderCompletedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($psCheckoutCart->getPaypalStatus(), PayPalOrderStatus::COMPLETED)) {
            return;
        }

        $this->module->getService('ps_checkout.bus.command')->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::COMPLETED,
            $event->getOrderPayPal()
        ));
    }

    public function saveApprovalReversedPayPalOrder(PayPalOrderApprovalReversedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($psCheckoutCart->getPaypalStatus(), PayPalOrderStatus::REVERSED)) {
            return;
        }

        $this->module->getService('ps_checkout.bus.command')->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::REVERSED,
            $event->getOrderPayPal()
        ));
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @throws PsCheckoutException
     * @throws PrestaShopException
     * @throws PayPalOrderException
     * @throws Exception
     */
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

        if ($psCheckoutCart->getPaypalStatus() === PayPalOrderStatus::COMPLETED) {
            return;
        }

        $this->checkoutChecker->continueWithAuthorization($psCheckoutCart->getIdCart(), $event->getOrderPayPal());

        $this->module->getService('ps_checkout.bus.command')->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @throws InvalidArgumentException
     */
    public function updateCache(PayPalOrderEvent $event)
    {
        $currentOrderPayPal = $this->orderPayPalCache->get($event->getOrderPayPalId()->getValue());
        $newOrderPayPal = $event->getOrderPayPal();

        if ($currentOrderPayPal && !$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($currentOrderPayPal['status'], $newOrderPayPal['status'])) {
            return;
        }

        $this->orderPayPalCache->set($event->getOrderPayPalId()->getValue(), $newOrderPayPal);
    }
}
