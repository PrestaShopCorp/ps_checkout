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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\QueryBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\AddOrderPaymentCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\CreateOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\UpdateOrderStatusCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentDeniedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentDeniedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentPendingQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentPendingQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentReversedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentReversedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private CheckOrderAmount $checkOrderAmount,
        private ChainAdapter $capturePayPalCache,
        private ChainAdapter $orderPayPalCache,
        private OrderStateMapper $orderStateMapper,
        private QueryBusInterface $queryBus,
        private CreateOrderCommandHandler $createOrderCommandHandler,
        private AddOrderPaymentCommandHandler $addOrderPaymentCommandHandler,
        private UpdateOrderStatusCommandHandler $updateOrderStatusCommandHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureCompletedEvent::class => [
                ['createOrder'],
                ['createOrderPayment'],
                ['setPaymentCompletedOrderStatus'],
                ['updateCache'],
            ],
            PayPalCaptureDeclinedEvent::class => [
                ['setPaymentDeclinedOrderStatus'],
                ['updateCache'],
            ],
            PayPalCapturePendingEvent::class => [
                ['createOrder'],
                ['setPaymentPendingOrderStatus'],
                ['updateCache'],
            ],
            PayPalCaptureReversedEvent::class => [
                ['setPaymentReversedOrderStatus'],
                ['updateCache'],
            ],
        ];
    }

    public function createOrder(PayPalCaptureEvent $event)
    {
        $this->createOrderCommandHandler->handle(new CreateOrderCommand(
            $event->getPayPalOrderId()->getValue(),
            $event->getCapture()
        ));
    }

    public function createOrderPayment(PayPalCaptureCompletedEvent $event)
    {
        try {
            /** @var GetOrderForPaymentCompletedQueryResult $order */
            $order = $this->queryBus->handle(new GetOrderForPaymentCompletedQuery($event->getPayPalOrderId()->getValue(), $event->getPayPalCaptureId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->getOrderPaymentId()) {
            return;
        }

        $capture = $event->getCapture();

        $this->addOrderPaymentCommandHandler->handle(new AddOrderPaymentCommand(
            $order->getOrderId()->getValue(),
            $capture['create_time'],
            $order->getPaymentMethod(),
            $capture['amount']['value'],
            $order->getCurrencyId(),
            $event->getPayPalCaptureId()->getValue()
        ));
    }

    public function setPaymentCompletedOrderStatus(PayPalCaptureCompletedEvent $event)
    {
        try {
            /** @var GetOrderForPaymentCompletedQueryResult $order */
            $order = $this->queryBus->handle(new GetOrderForPaymentCompletedQuery($event->getPayPalOrderId()->getValue(), $event->getPayPalCaptureId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->hasBeenPaid()) {
            return;
        }

        switch ($this->checkOrderAmount->checkAmount((string) $order->getTotalAmount(), (string) $event->getCapture()['amount']['value'])) {
            case CheckOrderAmount::ORDER_FULL_PAID:
            case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED)));
                break;
            case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID)));
                break;
        }
    }

    public function setPaymentPendingOrderStatus(PayPalCapturePendingEvent $event)
    {
        try {
            /** @var GetOrderForPaymentPendingQueryResult $order */
            $order = $this->queryBus->handle(new GetOrderForPaymentPendingQuery($event->getPayPalOrderId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->isInPending()) {
            return;
        }

        $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING)));
    }

    public function setPaymentDeclinedOrderStatus(PayPalCaptureDeclinedEvent $event)
    {
        try {
            /** @var GetOrderForPaymentDeniedQueryResult $order */
            $order = $this->queryBus->handle(new GetOrderForPaymentDeniedQuery($event->getPayPalOrderId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->hasBeenError()) {
            return;
        }

        $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR)));
    }

    public function setPaymentReversedOrderStatus(PayPalCaptureReversedEvent $event)
    {
        try {
            /** @var GetOrderForPaymentReversedQueryResult $order */
            $order = $this->queryBus->handle(new GetOrderForPaymentReversedQuery($event->getPayPalOrderId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if (!$order->hasBeenPaid() || $order->hasBeenTotallyRefund()) {
            return;
        }

        $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED)));
    }

    public function updateCache(PayPalCaptureEvent $event)
    {
        $this->capturePayPalCache->get($event->getPayPalCaptureId()->getValue(), function () use ($event) {
            return $event->getCapture();
        });

        $needToClearOrderPayPalCache = true;
        $orderPayPalCache = $this->orderPayPalCache->getItem($event->getPayPalOrderId()->getValue())->get();

        if ($orderPayPalCache && isset($orderPayPalCache['purchase_units'][0]['payments']['captures'])) {
            foreach ($orderPayPalCache['purchase_units'][0]['payments']['captures'] as $key => $capture) {
                if ($capture['id'] === $event->getPayPalCaptureId()->getValue()) {
                    $needToClearOrderPayPalCache = false;
                    $orderPayPalCache['purchase_units'][0]['payments']['captures'][$key] = $event->getCapture();
                    $this->capturePayPalCache->get($event->getPayPalCaptureId()->getValue(), function () use ($orderPayPalCache) {
                        return $orderPayPalCache;
                    });
                }
            }
        }

        if ($needToClearOrderPayPalCache) {
            $this->orderPayPalCache->delete($event->getPayPalOrderId()->getValue());
        }
    }
}
