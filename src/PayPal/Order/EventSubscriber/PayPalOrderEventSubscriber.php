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
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\SaveCheckoutCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\SavePayPalOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\SavePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Ps_checkout;
use Psr\SimpleCache\CacheInterface;
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

    /**
     * @var OrderStateMapper
     */
    private $orderStateMapper;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;

    public function __construct(
        Ps_checkout $module,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CacheInterface $orderPayPalCache,
        CheckoutChecker $checkoutChecker,
        CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService,
        OrderStateMapper $orderStateMapper,
        PayPalConfiguration $payPalConfiguration,
        PayPalOrderRepository $payPalOrderRepository
    ) {
        $this->module = $module;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->checkoutChecker = $checkoutChecker;
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
        $this->orderStateMapper = $orderStateMapper;
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->payPalConfiguration = $payPalConfiguration;
        $this->payPalOrderRepository = $payPalOrderRepository;
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
        $order = $event->getOrderPayPal();

        try { // NOT SURE WHAT SHOULD HAPPEN IF ORDER WITH THAT ID ALREADY EXISTS
            $payPalOrder = $this->payPalOrderRepository->getPayPalOrderByCartId($event->getCartId()->getValue());
            $this->payPalOrderRepository->deletePayPalOrder($payPalOrder->getId());
        } catch (Exception $e) {
        }

        $this->commandBus->handle(new SavePayPalOrderCommand(
            $order,
            $event->getCartId(),
            $event->getFundingSource(),
            $this->payPalConfiguration->getPaymentMode(),
            $event->getCustomerIntent(),
            $event->isExpressCheckout(),
            $event->isCardFields(),
            $event->getPaymentTokenId()
        ));

        $this->commandBus->handle(new SaveCheckoutCommand(
            $event->getCartId()->getValue(),
            $event->getOrderPayPalId()->getValue(),
            $order['status'],
            isset($order['intent']) ? $order['intent'] : $this->payPalConfiguration->getIntent(),
            $event->getFundingSource(),
            $event->isExpressCheckout(),
            $event->isCardFields(),
            $this->payPalConfiguration->getPaymentMode()
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

        try {
            $this->commandBus->handle(new SavePayPalOrderCommand($event->getOrderPayPal()));
        } catch (Exception $exception) {
        }

        $this->commandBus->handle(new SavePayPalOrderStatusCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::APPROVED
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

        try {
            $this->commandBus->handle(new SavePayPalOrderCommand($event->getOrderPayPal()));
        } catch (Exception $exception) {
        }

        $this->commandBus->handle(new SavePayPalOrderStatusCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::COMPLETED
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

        $this->commandBus->handle(new SavePayPalOrderStatusCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::REVERSED
        ));
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

        $this->commandBus->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    public function setApprovalReversedOrderStatus(PayPalOrderApprovalReversedEvent $event)
    {
        try {
            /** @var GetOrderForApprovalReversedQueryResult $order */
            $order = $this->commandBus->handle(
                new GetOrderForApprovalReversedQuery(
                    $event->getOrderPayPalId()->getValue()
                )
            );
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->hasBeenCanceled() || $order->hasBeenPaid()) {
            return;
        }

        $this->commandBus->handle(
            new UpdateOrderStatusCommand(
                $order->getOrderId()->getValue(),
                $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED)
            )
        );
    }

    public function updateCache(PayPalOrderEvent $event)
    {
        $currentOrderPayPal = $this->orderPayPalCache->get($event->getOrderPayPalId()->getValue());
        $newOrderPayPal = $event->getOrderPayPal();

        if ($currentOrderPayPal && !$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($currentOrderPayPal['status'], $newOrderPayPal['status'])) {
            return;
        }

        $this->orderPayPalCache->set($event->getOrderPayPalId()->getValue(), $newOrderPayPal);
    }

    public function updatePayPalOrder(PayPalOrderUpdatedEvent $event)
    {
        $this->commandBus->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPal(),
            $event->getCartId()
        ));
    }

    public function clearCache(PayPalOrderEvent $event)
    {
        $this->orderPayPalCache->delete($event->getOrderPayPalId()->getValue());
    }
}
