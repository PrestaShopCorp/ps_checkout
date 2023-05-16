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

namespace PrestaShop\Module\PrestashopCheckout\Order\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderPaymentCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderStatusUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Factory\OrderResumeFactory;
use PrestaShop\Module\PrestashopCheckout\Order\Matrice\Command\UpdateOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckTransitionStateService;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CheckTransitionStateService
     */
    private $checkTransitionStateService;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var OrderResumeFactory
     */
    private $orderResumeFactory;

    /**
     * @param CommandBusInterface $commandBus
     * @param CheckTransitionStateService $checkTransitionStateService
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     */
    public function __construct(
        CommandBusInterface $commandBus,
        CheckTransitionStateService $checkTransitionStateService,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        OrderResumeFactory $orderResumeFactory
    ) {
        $this->commandBus = $commandBus;
        $this->checkTransitionStateService = $checkTransitionStateService;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->orderResumeFactory = $orderResumeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderCreatedEvent::class => 'onOrderCreated',
            OrderPaymentCreatedEvent::class => 'onOrderPaymentCreated',
            OrderStatusUpdatedEvent::class => 'onOrderStatusUpdated',
        ];
    }

    /**
     * @param OrderCreatedEvent $event
     *
     * @return void
     *
     * @throws \PrestaShopException
     * @throws OrderException
     * @throws PayPalOrderException
     * @throws OrderStateException
     */
    public function onOrderCreated(OrderCreatedEvent $event)
    {
        $cartId = $event->getCartId()->getValue();

        /** @var \PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderQueryResult $order */
        $order = $this->commandBus->handle(new GetOrderQuery($cartId));

        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($cartId);
        $paypalOrder = $this->commandBus->handle(new GetPayPalOrderQuery($psCheckoutCart->paypal_order));
        $capturePayload = $paypalOrder->getOrder()['body']['purchase_units'][0]['payments']['captures'];

        $newOrderState = $this->checkTransitionStateService->getNewOrderState([
            'Order' => [
                'CurrentOrderStatus' => $getOrderStateConfiguration->getKeyById(new OrderStateId($order->getCurrentState())),
                'TotalAmountPaid' => $order->getTotalAmountPaid(),
                'TotalAmount' => $order->getTotalAmount(),
                'TotalRefunded' => '0',
            ],
            'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                null,
            ],
            'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                'Status' => $capturePayload['status'],
                'Amount' => $capturePayload['amount']['value'],
            ],
            'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                null,
            ],
            'PayPalOrder' => [
                'OldStatus' => $psCheckoutCart->getPaypalStatus(),
                'NewStatus' => $paypalOrder->getOrder()['status'],
            ],
        ]);
        if ($newOrderState !== false) {
            $newOrderStateId = $this->commandBus->handle(new GetOrderStateQuery($newOrderState));
            $this->commandBus->handle(new UpdateOrderStatusCommand($event->getOrderId()->getValue(), $newOrderStateId->getOrderStateId()->getValue()));
        }

        $this->commandBus->handle(new UpdateOrderMatriceCommand(
            $event->getOrderId()->getValue(),
            $psCheckoutCart->getPaypalOrderId()
        ));
    }

    /**
     * @param OrderPaymentCreatedEvent $event
     *
     * @return void
     */
    public function onOrderPaymentCreated(OrderPaymentCreatedEvent $event)
    {
        // TODO
    }

    /**
     * @param OrderStatusUpdatedEvent $event
     *
     * @return void
     */
    public function onOrderStatusUpdated(OrderStatusUpdatedEvent $event)
    {
        // TODO
    }
}
