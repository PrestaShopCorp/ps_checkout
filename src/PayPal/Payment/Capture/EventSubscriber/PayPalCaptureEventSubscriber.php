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
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfiguration;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckOrderState;
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
     * @var CheckOrderState
     */
    private $checkOrderState;

    /**
     * @param CommandBusInterface $commandBus
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     * @param CheckOrderState $checkOrderState
     */
    public function __construct(
        CommandBusInterface $commandBus,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CheckOrderState $checkOrderState
    ) {
        $this->commandBus = $commandBus;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->checkOrderState = $checkOrderState;
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

        $capture = $event->getCapture();

        $transactionId = $orderStateId = $paidAmount = '';
        $fundingSource = $psCheckoutCart->getPaypalFundingSource();
        $cart = new \Cart($psCheckoutCart->getIdCart());
        switch (true) {
            case empty($capture['amount']['value']):
                $orderStateId = $this->getPendingStatusId($fundingSource);
                break;
            case (float) $capture['amount']['value'] < $cart->getCartTotalPrice():
                $orderStateId = \Configuration::getGlobalValue(OrderStateConfiguration::PARTIALLY_PAID);
                break;
            case (float) $capture['amount']['value'] === $cart->getCartTotalPrice():
                $orderStateId = \Configuration::getGlobalValue(OrderStateConfiguration::PAYMENT_ACCEPTED);
                $transactionId = $event->getPayPalCaptureId();
                $paidAmount = $capture['amount']['value'];
                break;
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
        // TODO : PrestaShop Order status change to Payment accepted if paid completely

        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $orderId = $this->commandBus->handle(new GetOrderQuery($psCheckoutCart->getIdCart()));
        $order = new \Order($orderId);
        $currentOrderStateId = $order->getCurrentState();
        $newOrderStateId = $this->getNewState($event, $currentOrderStateId, $psCheckoutCart->getPaypalFundingSource());

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
