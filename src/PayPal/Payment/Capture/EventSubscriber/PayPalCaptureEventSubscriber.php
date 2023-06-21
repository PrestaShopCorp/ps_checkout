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

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentDeniedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentDeniedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentPendingQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentPendingQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentReversedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentReversedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use Ps_checkout;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var CheckOrderAmount
     */
    private $checkOrderAmount;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CacheInterface
     */
    private $capturePayPalCache;

    /**
     * @var OrderStateMapper
     */
    private $orderStateMapper;

    public function __construct(
        Ps_checkout $module,
        CheckOrderAmount $checkOrderAmount,
        CacheInterface $capturePayPalCache,
        OrderStateMapper $orderStateMapper
    ) {
        $this->module = $module;
        $this->checkOrderAmount = $checkOrderAmount;
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->capturePayPalCache = $capturePayPalCache;
        $this->orderStateMapper = $orderStateMapper;
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
            PayPalCaptureRefundedEvent::class => [
                ['setPaymentRefundedOrderStatus'],
                ['updateCache'],
            ],
            PayPalCaptureReversedEvent::class => [
                ['setPaymentReversedOrderStatus'],
                ['updateCache'],
            ],
        ];
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function createOrder(PayPalCaptureEvent $event)
    {
        $this->commandBus->handle(new CreateOrderCommand(
            $event->getPayPalOrderId()->getValue(),
            $event->getCapture()
        ));
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     *
     * @throws OrderException
     * @throws PayPalOrderException
     * @throws PayPalCaptureException
     */
    public function createOrderPayment(PayPalCaptureCompletedEvent $event)
    {
        /** @var GetOrderForPaymentCompletedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentCompletedQuery($event->getPayPalOrderId()->getValue(), $event->getPayPalCaptureId()->getValue()));

        if ($order->getOrderPaymentId()) {
            return;
        }

        $capture = $event->getCapture();

        $this->commandBus->handle(new AddOrderPaymentCommand(
            $order->getOrderId()->getValue(),
            $capture['create_time'],
            $order->getPaymentMethod(),
            $capture['amount']['value'],
            $order->getCurrencyId(),
            $event->getPayPalCaptureId()->getValue()
        ));
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @throws OrderException
     * @throws OrderStateException
     * @throws PayPalOrderException
     * @throws PayPalCaptureException
     */
    public function setPaymentCompletedOrderStatus(PayPalCaptureCompletedEvent $event)
    {
        /** @var GetOrderForPaymentCompletedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentCompletedQuery($event->getPayPalOrderId()->getValue(), $event->getPayPalCaptureId()->getValue()));

        if ($order->hasBeenPaid()) {
            return;
        }

        switch ($this->checkOrderAmount->checkAmount((string) $order->getTotalAmount(), (string) $event->getCapture()['amount']['value'])) {
            case CheckOrderAmount::ORDER_FULL_PAID:
            case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ACCEPTED)));
                break;
            case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID)));
                break;
        }
    }

    /**
     * @param PayPalCapturePendingEvent $event
     *
     * @throws OrderException
     * @throws OrderStateException
     * @throws PayPalOrderException
     */
    public function setPaymentPendingOrderStatus(PayPalCapturePendingEvent $event)
    {
        /** @var GetOrderForPaymentPendingQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentPendingQuery($event->getPayPalOrderId()->getValue()));

        if ($order->isInPending()) {
            return;
        }

        switch ($order->getPaymentMethod()) {
            case 'card':
                $newOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT);
                break;
            case 'paypal':
                $newOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT);
                break;
            default:
                $newOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT);
        }

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $newOrderStateId));
    }

    /**
     * @param PayPalCaptureDeclinedEvent $event
     *
     * @throws OrderException
     * @throws OrderStateException
     * @throws PayPalOrderException
     */
    public function setPaymentDeclinedOrderStatus(PayPalCaptureDeclinedEvent $event)
    {
        /** @var GetOrderForPaymentDeniedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentDeniedQuery($event->getPayPalOrderId()->getValue()));

        if ($order->hasBeenError()) {
            return;
        }

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ERROR)));
    }

    /**
     * @param PayPalCaptureRefundedEvent $event
     *
     * @throws OrderException
     * @throws OrderStateException
     * @throws PayPalOrderException
     */
    public function setPaymentRefundedOrderStatus(PayPalCaptureRefundedEvent $event)
    {
        /** @var GetOrderForPaymentRefundedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentRefundedQuery($event->getPayPalOrderId()->getValue()));

        if (!$order->hasBeenPaid() || $order->hasBeenTotallyRefund()) {
            return;
        }

        if ($this->checkOrderAmount->checkAmount($order->getTotalAmount(), $order->getTotalRefund()) == CheckOrderAmount::ORDER_NOT_FULL_PAID) {
            $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_REFUNDED)));
        } else {
            $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::REFUNDED)));
        }
    }

    /**
     * @param PayPalCaptureReversedEvent $event
     *
     * @throws OrderException
     * @throws OrderStateException
     * @throws PayPalCaptureException
     * @throws PayPalOrderException
     */
    public function setPaymentReversedOrderStatus(PayPalCaptureReversedEvent $event)
    {
        /** @var GetOrderForPaymentReversedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentReversedQuery($event->getPayPalOrderId()->getValue(), $event->getPayPalCaptureId()->getValue()));

        if (!$order->hasBeenPaid() || $order->hasBeenTotallyRefund()) {
            return;
        }

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getOrderId()->getValue(), $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::REFUNDED)));
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function updateCache(PayPalCaptureEvent $event)
    {
        $this->capturePayPalCache->set($event->getPayPalCaptureId()->getValue(), $event->getCapture());
    }
}
