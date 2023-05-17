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
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Exception\OrderPaymentException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckTransitionStateService;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Ps_checkout;
use Psr\Log\LoggerInterface;
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
        CheckOrderAmount $checkOrderAmount
    ) {
        $this->module = $module;
        $this->logger = $logger;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->checkTransitionStateService = $checkTransitionStateService;
        $this->checkOrderAmount = $checkOrderAmount;
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
                ['updateOrderStatus'],
            ],
            PayPalCaptureDeniedEvent::class => 'updateOrderStatus',
            PayPalCapturePendingEvent::class => [
                ['createOrder'],
                ['updateOrderStatus'],
            ],
            PayPalCaptureRefundedEvent::class => 'updateOrderStatus',
            PayPalCaptureReversedEvent::class => 'updateOrderStatus',
        ];
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     *
     * @throws \PrestaShopException
     */
    public function createOrder(PayPalCaptureEvent $event)
    {
        //        if (get_class($event) !== PayPalCaptureCompletedEvent::class || get_class($event) !== PayPalCapturePendingEvent::class) {
        //            throw new PsCheckoutException(sprintf('Invalid Capture Event class (%s). Expected : PayPalCaptureCompletedEvent or PayPalCapturePendingEvent', get_class($event)), PsCheckoutException::INVALID_CAPTURE_EVENT);
        //        }

        /** @var \PsCheckoutCart $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        try {
            $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

            $this->logger->info(sprintf('PrestaShop Order for PayPal Order #%s is already created.', $event->getPayPalOrderId()->getValue()));

            return; // If we already have an Order (when going from Pending to Completed), we stop
        } catch (PsCheckoutException $exception) {
        }

        $capture = $event->getCapture();

        $transactionId = $orderStateId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();
        $cart = new \Cart($psCheckoutCart->getIdCart());

        $getOrderStateConfiguration = $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderStateConfigurationQuery());

        if (empty($capture['amount']['value'])) {
            $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
        } else {
            switch ($this->checkOrderAmount->checkAmount((string) $capture['amount']['value'], (string) $cart->getCartTotalPrice())) {
                case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
                    break;
                case CheckOrderAmount::ORDER_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ACCEPTED);
                    $transactionId = $event->getPayPalCaptureId()->getValue();
                    $paidAmount = $capture['amount']['value'];
                    break;
                case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
            }
        }

        $this->module->getService('ps_checkout.bus.command')->handle(new CreateOrderCommand(
            $psCheckoutCart->getIdCart(),
            'ps_checkout',
            $orderStateId,
            $fundingSource,
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
     * @throws \PrestaShopException
     * @throws PayPalCaptureException
     */
    public function createOrderPayment(PayPalCaptureCompletedEvent $event)
    {
        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        /** @var GetOrderQueryResult $order */
        $order = $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

        try {
            $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderPaymentQuery($event->getPayPalCaptureId()->getValue()));

            $this->logger->info('Order Payment is already created.');

            return; // We already have an OrderPayment, there's no need to add another one
        } catch (OrderPaymentException $e) {
        }

        $paymentMethod = $psCheckoutCart->paypal_funding;
        if ($paymentMethod === 'card') {
            $paymentMethod .= $psCheckoutCart->isHostedFields ? '_hosted' : '_inline';
        }

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

        $this->module->getService('ps_checkout.bus.command')->handle(new AddOrderPaymentCommand(
            $order->getId(),
            $createTime->format('Y-m-d H:i:s'),
            $paymentMethod,
            $paymentAmount,
            $order->getCurrencyId(),
            $transactionId
        ));
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     * @throws CartException
     * @throws OrderException
     * @throws OrderStateException
     */
    public function updateOrderStatus(PayPalCaptureEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        /** @var GetOrderStateConfigurationQueryResult $getOrderStateConfiguration */
        $getOrderStateConfiguration = $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderStateConfigurationQuery());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $orderQueryResult = $this->module->getService('ps_checkout.bus.command')->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));
        $order = new \Order($orderQueryResult->getId());
        $currentOrderStateId = $order->getCurrentState();

        $paypalOrder = $this->module->getService('ps_checkout.bus.command')->handle(new GetPayPalOrderQuery($event->getPayPalOrderId()->getValue()));
        $capturePayload = $paypalOrder->getOrder()['purchase_units'][0]['payments']['captures'][0];

        $newOrderState = $this->checkTransitionStateService->getNewOrderState([
            'Order' => [
                'CurrentOrderStatus' => $getOrderStateConfiguration->getKeyById(new OrderStateId($order->getCurrentState())),
                'TotalAmountPaid' => $order->getTotalPaid(),
                'TotalAmount' => $order->total_paid_tax_incl, // Peut etre récupérer le total du panier via l'instance Cart
                'TotalRefunded' => '0',
            ],
            'PayPalCapture' => [
                'Status' => $capturePayload['status'],
                'Amount' => $capturePayload['amount']['value'],
            ],
            'PayPalOrder' => [
                'OldStatus' => $psCheckoutCart->getPaypalStatus(),
                'NewStatus' => $paypalOrder->getOrder()['status'],
            ],
        ]);

        if ($newOrderState !== false) {
            $newOrderStateId = $getOrderStateConfiguration->getIdByKey($newOrderState);
            $this->module->getService('ps_checkout.bus.command')->handle(new UpdateOrderStatusCommand($currentOrderStateId, $newOrderStateId));
        } else {
            throw new OrderStateException(sprintf('Order state from order #%s cannot be changed from %s ', $orderQueryResult->getId(), $currentOrderStateId), OrderStateException::TRANSITION_UNAVAILABLE);
        }
    }
}
