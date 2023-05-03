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
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

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
     * @param CommandBusInterface $commandBus
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     * @param CheckTransitionStateService $checkTransitionStateService
     */
    public function __construct(
        CommandBusInterface $commandBus,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CheckTransitionStateService $checkTransitionStateService,
        CheckOrderAmout $checkOrderAmount
    ) {
        $this->commandBus = $commandBus;
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
            PayPalCapturePendingEvent::class => 'updateOrderStatus',
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
    public function createOrder(PayPalCaptureCompletedEvent $event)
    {
        /** @var \PsCheckoutCart $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        $capture = $event->getCapture();

        $transactionId = $orderStateId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();
        $cart = new \Cart($psCheckoutCart->getIdCart());

        if (empty($capture['amount']['value'])) {
            $orderStateId = $this->getPendingStatusId($fundingSource);
        } else {
            switch ($this->checkOrderAmount->checkAmount((string) $capture['amount']['value'], (string) $cart->getCartTotalPrice())) {
                case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
                    break;
                case CheckOrderAmount::ORDER_FULL_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PAYMENT_ACCEPTED);
                    $transactionId = $event->getPayPalCaptureId();
                    $paidAmount = $capture['amount']['value'];
                    break;
                case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                    $orderStateId = $getOrderStateConfiguration->getIdByKey(OrderStateConfigurationKeys::PARTIALLY_PAID);
            }
        }

        $this->commandBus->handle(new CreateOrderCommand(
            $psCheckoutCart->getIdCart(),
            'ps_checkout',
            $orderStateId,
            $fundingSource,
            $transactionId,
            $paidAmount
        ));
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     *
     * @throws CartException
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     * @throws PayPalCaptureException
     */
    public function createOrderPayment(PayPalCaptureEvent $event)
    {
        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());
        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        /** @var GetOrderQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));

        $orderPayment = null;
        try {
            $this->commandBus->handle(new GetOrderPaymentQuery($event->getPayPalCaptureId()));

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
        // @TODO: Test if it correctly works everytime (because of float approx.)
        if ((float) $capture['amount']['value'] === ((float) $order->getTotalAmount() - (float) $order->getTotalAmountPaid())) {
            $paymentAmount = $capture['amount']['value'];
            $transactionId = $event->getPayPalCaptureId();
        }

        $createTime = new \DateTime($capture['create_time']);
        if (empty($orderPayment) && $capture['status'] === 'COMPLETED') {
            $this->commandBus->handle(new AddOrderPaymentCommand(
                $order->getId(),
                $createTime->format('Y-m-d H:i:s'),
                $paymentMethod,
                $paymentAmount,
                $order->getCurrencyId(),
                $transactionId
            ));
        }
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
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $orderId = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));
        $order = new \Order($orderId);
        $currentOrderStateId = $order->getCurrentState();

        //ATTENTION LES YEUX !!! (A l'aide)
        $total_refund = 0;
        foreach ($order->getOrderSlipsCollection() as $orderSlip) {
            $total_refund += $orderSlip->amount;
        }
        $newOrderState = $this->checkTransitionStateService->getNewOrderState([
            'cart' => ['amount' => $order->total_paid],
            'Order' => [
                'currentOrderStatus' => $getOrderStateConfiguration->getKeyById(new OrderStateId($currentOrderStateId)),
                'totalAmountPaid' => $order->getTotalPaid(),
                'totalAmount' => $order->total_paid,
                'totalRefunded' => $total_refund,
            ],
            'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                null,
            ],
            'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                'status' => $event->getCapture()['status'],
                'amount' => $event->getCapture()['amount']['value'],
            ],
            'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                null,
            ],
            'PayPalOrder' => [
                'oldStatus' => PayPalOrderStatus::CREATED,
                'newStatus' => PayPalOrderStatus::COMPLETED,
            ],
        ]);

        $newOrderStateId = $getOrderStateConfiguration->getIdByKey($newOrderState);

        // Prevent duplicate state entry
        if ($currentOrderStateId !== $newOrderStateId
            && false === (bool) $order->hasBeenPaid()
            && false === (bool) $order->hasBeenShipped()
            && false === (bool) $order->hasBeenDelivered()
            && false === (bool) $order->isInPreparation()
        ) {
            if ($this->checkOrderState->isOrderStateTransitionAvailable($currentOrderStateId, $newOrderStateId)) {
                $this->commandBus->handle(new UpdateOrderStatusCommand($currentOrderStateId, $newOrderStateId));
            } else {
                throw new OrderStateException(sprintf('Order state from order #%s cannot be changed from %s to %s', $orderId, $currentOrderStateId, $newOrderStateId), OrderStateException::TRANSITION_UNAVAILABLE);
            }
        }
    }

    /**
     * @param PayPalCaptureEvent $event
     * @param int $currentOrderStateId
     * @param string $fundingSource
     *
     * @return int
     */
    private function getNewState($event, $currentOrderStateId, $fundingSource)
    {
        $eventClass = get_class($event);
        switch ($eventClass) {
            case PayPalCaptureReversedEvent::class:
                return (int) \Configuration::getGlobalValue('PS_OS_CANCELED');
            case PayPalCaptureCompletedEvent::class:
                return $this->getPaidStatusId($currentOrderStateId);
            case PayPalCaptureDeniedEvent::class:
                return (int) \Configuration::getGlobalValue('PS_OS_ERROR');
            case PayPalCaptureRefundedEvent::class:
                return (int) \Configuration::getGlobalValue('PS_OS_REFUND');
            default:
                return $this->getPendingStatusId($fundingSource);
        }
    }

    /**
     * @param int $currentOrderStateId Current OrderState identifier
     *
     * @return int OrderState paid identifier
     */
    private function getPaidStatusId($currentOrderStateId)
    {
        if ($currentOrderStateId === (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_UNPAID')) {
            return (int) \Configuration::getGlobalValue('PS_OS_OUTOFSTOCK_PAID');
        }

        return (int) \Configuration::getGlobalValue('PS_OS_PAYMENT');
    }

    /**
     * @param string $fundingSource
     * @TODO to be removed as we will be keeping only one PS_CHECKOUT_STATE_WAITING_PAYMENT
     *
     * @return int OrderState identifier
     */
    private function getPendingStatusId($fundingSource)
    {
        switch ($fundingSource) {
            case 'card':
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT');
                break;
            case 'paypal':
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT');
                break;
            default:
                $orderStateId = (int) \Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT');
        }

        return $orderStateId;
    }
}
