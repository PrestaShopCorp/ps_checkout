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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventProcessor;

use PrestaShop\Module\PrestashopCheckout\Checkout\Command\SaveCheckoutCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\SavePayPalOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\CommandHandler\SaveCheckoutCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Checkout\CommandHandler\SavePayPalOrderStatusCommandHandler;
use PrestaShop\Module\PrestashopCheckout\CommandBus\QueryBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\UpdateOrderStatusCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\SavePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\SavePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\Cache\Adapter\ChainAdapter;

class PayPalOrderEventProcessor
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private PsCheckoutCartRepository $psCheckoutCartRepository,
        private ChainAdapter $orderPayPalCache,
        private CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService,
        private OrderStateMapper $orderStateMapper,
        private PayPalConfiguration $payPalConfiguration,
        private PayPalOrderRepository $payPalOrderRepository,
        private SavePayPalOrderCommandHandler $savePayPalOrderCommandHandler,
        private SaveCheckoutCommandHandler $saveCheckoutCommandHandler,
        private SavePayPalOrderStatusCommandHandler $savePayPalOrderStatusCommandHandler,
        private UpdateOrderStatusCommandHandler $updateOrderStatusCommandHandler,
    ) {
    }

    public function saveCreatedPayPalOrder(PayPalOrderCreatedEvent $event)
    {
        $order = $event->getOrderPayPal();

        try {
            $payPalOrder = $this->payPalOrderRepository->getPayPalOrderByCartId($event->getCartId()->getValue());
            $this->payPalOrderRepository->deletePayPalOrder($payPalOrder->getId());
        } catch (\Exception $e) {
        }

        $this->savePayPalOrderCommandHandler->handle(new SavePayPalOrderCommand(
            $order,
            $event->getCartId(),
            $event->getFundingSource(),
            $this->payPalConfiguration->getPaymentMode(),
            $event->getCustomerIntent(),
            $event->isExpressCheckout(),
            $event->isCardFields(),
            $event->getPaymentTokenId()
        ));

        $this->saveCheckoutCommandHandler->handle(new SaveCheckoutCommand(
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
            $this->savePayPalOrderCommandHandler->handle(new SavePayPalOrderCommand($event->getOrderPayPal()));
        } catch (\Exception $exception) {
        }

        $this->savePayPalOrderStatusCommandHandler->handle(new SavePayPalOrderStatusCommand(
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
            $this->savePayPalOrderCommandHandler->handle(new SavePayPalOrderCommand($event->getOrderPayPal()));
        } catch (\Exception $exception) {
        }

        $this->savePayPalOrderStatusCommandHandler->handle(new SavePayPalOrderStatusCommand(
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

        $this->savePayPalOrderStatusCommandHandler->handle(new SavePayPalOrderStatusCommand(
            $event->getOrderPayPalId()->getValue(),
            PayPalOrderStatus::REVERSED
        ));
    }

    public function setApprovalReversedOrderStatus(PayPalOrderApprovalReversedEvent $event)
    {
        try {
            /** @var GetOrderForApprovalReversedQueryResult $order */
            $order = $this->queryBus->handle(
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

        $this->updateOrderStatusCommandHandler->handle(
            new UpdateOrderStatusCommand(
                $order->getOrderId()->getValue(),
                $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED)
            )
        );
    }

    public function updatePayPalOrder(PayPalOrderEvent $event)
    {
        $this->savePayPalOrderCommandHandler->handle(new SavePayPalOrderCommand(
            $event->getOrderPayPal()
        ));
    }

    public function updateCache(PayPalOrderEvent $event)
    {
        $currentOrderPayPalCacheItem = $this->orderPayPalCache->getItem($event->getOrderPayPalId()->getValue());
        $currentOrderPayPal = $currentOrderPayPalCacheItem->get();
        $newOrderPayPal = $event->getOrderPayPal();

        if ($currentOrderPayPal && !$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($currentOrderPayPal['status'], $newOrderPayPal['status'])) {
            return;
        }

        $currentOrderPayPalCacheItem->set($newOrderPayPal);
        $this->orderPayPalCache->save($currentOrderPayPalCacheItem);
    }

    public function clearCache(PayPalOrderEvent $event)
    {
        $this->orderPayPalCache->delete($event->getOrderPayPalId()->getValue());
    }
}
