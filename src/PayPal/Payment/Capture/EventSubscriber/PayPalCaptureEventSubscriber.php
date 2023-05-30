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

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Exception\OrderPaymentException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQuery;
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
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopException;
use Ps_checkout;
use PsCheckoutCart;
use Psr\Log\LoggerInterface;
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
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var CheckOrderAmount
     */
    private $checkOrderAmount;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CacheInterface
     */
    private $capturePayPalCache;

    /**
     * @param Ps_checkout $module
     * @param LoggerInterface $logger
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     * @param CheckOrderAmount $checkOrderAmount
     * @param CacheInterface $capturePayPalCache
     */
    public function __construct(
        Ps_checkout $module,
        LoggerInterface $logger,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CheckOrderAmount $checkOrderAmount,
        CacheInterface $capturePayPalCache
    ) {
        $this->module = $module;
        $this->logger = $logger;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->checkOrderAmount = $checkOrderAmount;
        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->commandBus = $commandBus;
        $this->capturePayPalCache = $capturePayPalCache;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureCompletedEvent::class => [
                ['createPaidOrder'],
                ['createOrderPayment'],
                ['setPaymentCompletedOrderStatus'],
                ['updateCache'],
            ],
            PayPalCaptureDeclinedEvent::class => [
                ['setPaymentDeclinedOrderStatus'],
                ['updateCache'],
            ],
            PayPalCapturePendingEvent::class => [
                ['createPendingOrder'],
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
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function createPaidOrder(PayPalCaptureCompletedEvent $event)
    {
        /** @var PsCheckoutCart $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        $cart = new \Cart($psCheckoutCart->getIdCart());

        if (!\Validate::isLoadedObject($cart)) {
            throw new PsCheckoutException('Cart not found', PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        try {
            /** @var GetOrderQueryResult $order */
            $order = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

            $this->logger->info(
                'PrestaShop Order for PayPal Order is already created.',
                [
                    'id_cart' => $psCheckoutCart->getIdCart(),
                    'id_order' => $order->getId(),
                    'PayPalOrderId' => $event->getPayPalOrderId()->getValue(),
                ]
            );

            return; // If we already have an Order (when going from Pending to Completed), we stop
        } catch (PsCheckoutException $exception) {
            $this->logger->info(
                'No PrestaShop Order for PayPal Order, create it',
                [
                    'id_cart' => $psCheckoutCart->getIdCart(),
                    'PayPalOrderId' => $event->getPayPalOrderId()->getValue(),
                ]
            );
        }

        $capture = $event->getCapture();

        $transactionId = $orderStateId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();

        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        if (empty($capture['amount']['value'])) {
            $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
        } else {
            switch ($this->checkOrderAmount->checkAmount((string) $capture['amount']['value'], (string) $cart->getOrderTotal(true, \Cart::BOTH))) {
                case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
                    break;
                case CheckOrderAmount::ORDER_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ACCEPTED);
                    $transactionId = $event->getPayPalCaptureId()->getValue();
                    $paidAmount = $capture['amount']['value'];
                    break;
                case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ACCEPTED);
            }
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');

        $this->commandBus->handle(new CreateOrderCommand(
            $psCheckoutCart->getIdCart(),
            'ps_checkout',
            $orderStateId,
            $fundingSourceTranslationProvider->getPaymentMethodName($fundingSource),
            $transactionId,
            $paidAmount
        ));
    }

    /**
     * @param PayPalCapturePendingEvent $event
     *
     * @return void
     *
     * @throws PrestaShopException
     */
    public function createPendingOrder(PayPalCapturePendingEvent $event)
    {
        /** @var PsCheckoutCart $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        $cart = new \Cart($psCheckoutCart->getIdCart());

        if (!\Validate::isLoadedObject($cart)) {
            throw new PsCheckoutException('Cart not found', PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        try {
            /** @var GetOrderQueryResult $order */
            $order = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

            $this->logger->info(
                'PrestaShop Order for PayPal Order is already created.',
                [
                    'id_cart' => $psCheckoutCart->getIdCart(),
                    'id_order' => $order->getId(),
                    'PayPalOrderId' => $event->getPayPalOrderId()->getValue(),
                ]
            );

            return;
        } catch (PsCheckoutException $exception) {
        }

        $transactionId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();

        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        switch ($fundingSource) {
            case 'card':
                $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT);
                break;
            case 'paypal':
                $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT);
                break;
            default:
                $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT);
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');

        $this->commandBus->handle(new CreateOrderCommand(
            $psCheckoutCart->getIdCart(),
            'ps_checkout',
            $orderStateId,
            $fundingSourceTranslationProvider->getPaymentMethodName($fundingSource),
            $transactionId,
            $paidAmount
        ));
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     *
     * @throws CartException
     * @throws PsCheckoutException
     * @throws PrestaShopException
     * @throws PayPalCaptureException
     */
    public function createOrderPayment(PayPalCaptureCompletedEvent $event)
    {
        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        /** @var GetOrderQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

        try {
            $this->commandBus->handle(new GetOrderPaymentQuery($order->getId(), $event->getPayPalCaptureId()->getValue()));

            $this->logger->info(
                'Order Payment is already created.',
                [
                    'id_order' => $order->getId(),
                    'PayPalCaptureId' => $event->getPayPalCaptureId()->getValue(),
                ]);

            return; // We already have an OrderPayment, there's no need to add another one
        } catch (OrderPaymentException $e) {
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');

        $capture = $event->getCapture();
        $paymentAmount = '';
        $transactionId = null;
        $captureAmount = sprintf('%01.2f', $capture['amount']['value']);
        $orderAmount = sprintf('%01.2f', $order->getTotalAmount());
        $paidAmount = sprintf('%01.2f', $order->getTotalAmountPaid());

        if ($captureAmount + 0.05 < ($orderAmount - $paidAmount) || $captureAmount - 0.05 > ($orderAmount - $paidAmount)) {
            $paymentAmount = $capture['amount']['value'];
            $transactionId = $event->getPayPalCaptureId()->getValue();
        }

        $createTime = new \DateTime($capture['create_time']);

        $this->commandBus->handle(new AddOrderPaymentCommand(
            $order->getId(),
            $createTime->format('Y-m-d H:i:s'),
            $fundingSourceTranslationProvider->getPaymentMethodName($psCheckoutCart->paypal_funding),
            $paymentAmount,
            $order->getCurrencyId(),
            $transactionId
        ));
    }

    /**
     * @throws PrestaShopException
     * @throws OrderException
     * @throws OrderStateException
     * @throws PsCheckoutException
     * @throws CartException
     */
    public function setPaymentCompletedOrderStatus(PayPalCaptureCompletedEvent $event)
    {
        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        /** @var GetOrderForPaymentCompletedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentCompletedQuery($psCheckoutCart->getIdCart()));

        if ($order->hasBeenPaid()) {
            return;
        }

        switch ($this->checkOrderAmount->checkAmount((string) $order->getTotalAmount(), (string) $event->getCapture()['amount']['value'] + $order->getTotalAmountPaid())) {
            case CheckOrderAmount::ORDER_FULL_PAID:
            case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getPaymentAcceptedState()->getOrderStateId()));
                break;
            case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getPartiallyPaidState()->getOrderStateId()));
                break;
        }
    }

    /**
     * @throws PrestaShopException
     * @throws OrderException
     * @throws OrderStateException
     * @throws CartException
     */
    public function setPaymentPendingOrderStatus(PayPalCapturePendingEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        /** @var GetOrderForPaymentPendingQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentPendingQuery($psCheckoutCart->getIdCart()));

        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        switch ($psCheckoutCart->getPaypalFundingSource()) {
            case 'card':
                $newOrderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT);
                break;
            case 'paypal':
                $newOrderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT);
                break;
            default:
                $newOrderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT);
        }

        if ($order->isInPending()) {
            return;
        }

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $newOrderStateId));
    }

    public function setPaymentDeclinedOrderStatus(PayPalCaptureDeclinedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        try {
            /** @var GetOrderForPaymentDeniedQueryResult $order */
            $order = $this->commandBus->handle(new GetOrderForPaymentDeniedQuery($psCheckoutCart->getIdCart()));
        } catch (PsCheckoutException $exception) {
            return;
        }

        // Si timeout lors de la capture, on a créé une commande PS avec un status pending
        // Donc on doit mettre à jour le status de la commande PS en PAYMENT_ERROR

        // Si OrderStateHistory y a déjà PAYMENT_ERROR
        if ($order->hasBeenError()) {
            return;
        }

        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getPaymentErrorState()->getOrderStateId()));
    }

    /**
     * @throws PrestaShopException
     * @throws OrderException
     * @throws OrderStateException
     * @throws PsCheckoutException
     * @throws CartException
     */
    public function setPaymentRefundedOrderStatus(PayPalCaptureRefundedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());
        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }
        /** @var GetOrderForPaymentRefundedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentRefundedQuery($psCheckoutCart->getIdCart()));

        if (!$order->hasBeenPaid()) {
            return;
        }
        if ($order->hasBeenTotallyRefund()) {
            return;
        }
        if ($this->checkOrderAmount->checkAmount($order->getTotalAmount(), $order->getTotalRefund()) == CheckOrderAmount::ORDER_NOT_FULL_PAID) {
            $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getPartiallyRefundedState()->getOrderStateId()));
        } else {
            $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getRefundedState()->getOrderStateId()));
        }
    }

    /**
     * @throws PrestaShopException
     * @throws OrderException
     * @throws OrderStateException
     * @throws PsCheckoutException
     * @throws CartException
     */
    public function setPaymentReversedOrderStatus(PayPalCaptureReversedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        /** @var GetOrderForPaymentReversedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentReversedQuery($psCheckoutCart->getIdCart()));

        if (!$order->hasBeenPaid()) {
            return;
        }

        if ($order->hasBeenTotallyRefund()) {
            return;
        }

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getRefundedState()->getOrderStateId()));
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
        $this->logger->info(
            __CLASS__ . ':' . __FUNCTION__,
            [
                'PayPalOrderId' => $event->getPayPalOrderId()->getValue(),
                'PayPalCaptureId' => $event->getPayPalCaptureId()->getValue(),
                'PayPalCapture' => $event->getCapture(),
            ]
        );
        $this->capturePayPalCache->set($event->getPayPalCaptureId()->getValue(), $event->getCapture());
    }
}
