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
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckTransitionStateService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheInterface;
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
     * @var CheckTransitionStateService
     */
    private $checkTransitionStateService;

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
    private $cache;

    /**
     * @param Ps_checkout $module
     * @param LoggerInterface $logger
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     * @param CheckTransitionStateService $checkTransitionStateService
     * @param CheckOrderAmount $checkOrderAmount
     */
    public function __construct(
        Ps_checkout $module,
        LoggerInterface $logger,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CheckTransitionStateService $checkTransitionStateService,
        CheckOrderAmount $checkOrderAmount,
        CacheInterface $cache
    ) {
        $this->module = $module;
        $this->logger = $logger;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->checkTransitionStateService = $checkTransitionStateService;
        $this->checkOrderAmount = $checkOrderAmount;
        /** @var CommandBusInterface $commandBus */
        $commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->commandBus = $commandBus;
        $this->cache = $cache;
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
                ['updateCache']
            ],
            PayPalCaptureDeclinedEvent::class => [
                ['setPaymentDeclinedOrderStatus'],
                ['updateCache']
            ],
            PayPalCapturePendingEvent::class => [
                ['createPendingOrder'],
                ['setPaymentPendingOrderStatus'],
            ],
            PayPalCaptureRefundedEvent::class => [
                ['setPaymentRefundedOrderStatus'],
                ['updateCache']
            ],
            PayPalCaptureReversedEvent::class => [
                ['setPaymentReversedOrderStatus'],
                ['updateCache']
            ]
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

        try {
            $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

            $this->logger->info(sprintf('PrestaShop Order for PayPal Order #%s is already created.', $event->getPayPalOrderId()->getValue()));

            return; // If we already have an Order (when going from Pending to Completed), we stop
        } catch (PsCheckoutException $exception) {
        }

        $capture = $event->getCapture();

        $transactionId = $orderStateId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();
        $cart = new \Cart($psCheckoutCart->getIdCart());

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

        try {
            $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

            $this->logger->info(sprintf('PrestaShop Order for PayPal Order #%s is already created.', $event->getPayPalOrderId()->getValue()));

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
            $this->commandBus->handle(new GetOrderPaymentQuery($event->getPayPalCaptureId()->getValue()));

            $this->logger->info('Order Payment is already created.');

            return; // We already have an OrderPayment, there's no need to add another one
        } catch (OrderPaymentException $e) {
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');

        $capture = $event->getCapture();
        $paymentAmount = '';
        $transactionId = null;

        $this->logger->debug('Capture array : ' . print_r($capture, true));
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

//    /**
//     * @param PayPalCaptureEvent $event
//     *
//     * @return void
//     *
//     * @throws PsCheckoutException
//     * @throws \PrestaShopDatabaseException
//     * @throws \PrestaShopException
//     * @throws CartException
//     * @throws OrderException
//     * @throws OrderStateException
//     */
//    public function updateOrderStatus(PayPalCaptureEvent $event)
//    {
//        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
//        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
//        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());
//
//        if (false === $psCheckoutCart) {
//            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
//        }
//
//        /** @var GetOrderQueryResult $order */
//        $order = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));
//
//        $currentOrderStateId = $order->getCurrentStateId();
//
//        $paypalOrder = $this->commandBus->handle(new GetPayPalOrderQuery($event->getPayPalOrderId()->getValue()));
//        $capturePayload = $paypalOrder->getOrder()['purchase_units'][0]['payments']['captures'][0];
//
//        // Normal case
//        if ($this->checkOrderAmount->checkAmount((string) $order->getTotalAmountPaid(), (string) $order->getTotalAmount()) === CheckOrderAmount::ORDER_FULL_PAID
//            && $order->getCurrentStateId() === $getOrderStateConfiguration->getPaymentAcceptedState()->getOrderStateId()
//            ) {
//            return;
//        }
//
//        $this->module->getLogger()->debug(__CLASS__, [
//            'Order' => [
//                'CurrentOrderStatus' => $getOrderStateConfiguration->getKeyById(new OrderStateId($order->getCurrentStateId())),
//                'TotalAmountPaid' => $order->getTotalAmountPaid(),
//                'TotalAmount' => $order->getTotalAmount(), // Peut etre récupérer le total du panier via l'instance Cart
//                'TotalRefunded' => '0',
//            ],
//            'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
//                null,
//            ],
//            'PayPalCapture' => [
//                'Status' => $capturePayload['status'],
//                'Amount' => $capturePayload['amount']['value'],
//            ],
//            'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
//                null,
//            ],
//            'PayPalOrder' => [
//                'OldStatus' => $psCheckoutCart->getPaypalStatus(),
//                'NewStatus' => $paypalOrder->getOrder()['status'],
//            ],
//        ]);
//
//        $newOrderState = $this->checkTransitionStateService->getNewOrderState([
//            'Order' => [
//                'CurrentOrderStatus' => $getOrderStateConfiguration->getKeyById(new OrderStateId($order->getCurrentStateId())),
//                'TotalAmountPaid' => $order->getTotalAmountPaid(),
//                'TotalAmount' => $order->getTotalAmount(), // Peut etre récupérer le total du panier via l'instance Cart
//                'TotalRefunded' => '0',
//            ],
//            'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
//                null,
//            ],
//            'PayPalCapture' => [
//                'Status' => $capturePayload['status'],
//                'Amount' => $capturePayload['amount']['value'],
//            ],
//            'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
//                null,
//            ],
//            'PayPalOrder' => [
//                'OldStatus' => $psCheckoutCart->getPaypalStatus(),
//                'NewStatus' => $paypalOrder->getOrder()['status'],
//            ],
//        ]);
//
//        /*
//         *
//        $currentOrderStateId = $order->getCurrentStateId();
//        $order->hasBeenPaid();
//        $order->hasBeenShipped();
//        $order->hasBeenDelivered();
//        $order->isInPreparation();
//         */
//        if ($newOrderState !== false) {
//            $newOrderStateId = $getOrderStateConfiguration->getIdByKey($newOrderState);
//            $this->commandBus->handle(new UpdateOrderStatusCommand($currentOrderStateId, $newOrderStateId));
//        } else {
//            throw new OrderStateException(sprintf('Order state from order #%s cannot be changed (%s => %s)  ', $order->getId(), $psCheckoutCart->getPaypalStatus(), $paypalOrder->getOrder()['status']), OrderStateException::TRANSITION_UNAVAILABLE);
//        }
//    }

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

        $this->logger->debug(
            __CLASS__,
            [
                'function' => __FUNCTION__,
                'id_cart' => $psCheckoutCart->getIdCart(),
                'query' => new GetOrderForPaymentCompletedQuery($psCheckoutCart->getIdCart()),
                'commandBus' => $this->commandBus instanceof CommandBusInterface,
                'getOrderStateConfiguration' => $getOrderStateConfiguration,
            ]
        );

        /** @var GetOrderForPaymentCompletedQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderForPaymentCompletedQuery($psCheckoutCart->getIdCart()));

        if ($order) {
            $this->logger->debug(__CLASS__, [__FUNCTION__, get_class($order)]);
        } else {
            $this->logger->error(__CLASS__, [__FUNCTION__, $order]);
        }

        if ($order->hasBeenPaid()) {
            return;
        }

        // todo check order amount

        $this->commandBus->handle(new UpdateOrderStatusCommand($order->getId(), $getOrderStateConfiguration->getPaymentAcceptedState()->getOrderStateId()));
    }

    /**
     * @throws PrestaShopException
     * @throws OrderException
     * @throws OrderStateException
     * @throws CartException
     */
    public function setPaymentPendingOrderStatus(PayPalCapturePendingEvent $event)
    {
        // PayPalCapturePendingEvent
        // - Vérifier si le ps Order existe
        // - Vérifier le current PS Order State === WAITING_PAYPAL_PAYMENT ou WAITING_LOCAL_PAYMENT ou WAITING_CREDIT_CARD_PAYMENT
        // ===> Changer le PS Order state si besoin

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
     * @return void
     * @throws InvalidArgumentException
     */
    public function updateCache(PayPalCaptureEvent $event){
        $this->cache->set($event->getPayPalOrderId()->getValue(),$event->getCapture());
    }
}
