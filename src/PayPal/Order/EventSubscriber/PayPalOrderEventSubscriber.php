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

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdatePayPalOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\PrunePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderFetchedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderNotApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
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
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        CommandBusInterface $commandBus,
        PsCheckoutCartRepository $psCheckoutCartRepository
    ) {
        $this->commandBus = $commandBus;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => [
                ['updatePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderApprovedEvent::class => [
                ['updatePayPalOrder'],
                ['capturePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderNotApprovedEvent::class => [
                ['updatePayPalOrder'],
            ],
            PayPalOrderCompletedEvent::class => [
                ['updatePayPalOrder'],
                ['updatePayPalOrderMatrice'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderApprovalReversedEvent::class => [
                ['updatePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderFetchedEvent::class => [
                ['updatePayPalOrderCache'],
            ],
        ];
    }

    /**
     * @param $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException
     */
    public function updatePayPalOrder($event)
    {
        // @todo We don't have a dedicated table for order data storage in database yet
        // But we can save some data in current pscheckout_cart table

        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        switch (get_class($event)) {
            case PayPalOrderCreatedEvent::class:
                $orderStatus = 'CREATED';
                break;
            case PayPalOrderApprovedEvent::class:
                $orderStatus = 'APPROVED';
                break;
            case PayPalOrderCompletedEvent::class:
                $orderStatus = 'COMPLETED';
                break;
            case PayPalOrderApprovalReversedEvent::class:
                $orderStatus = 'PENDING_APPROVAL';
                break;
            case PayPalOrderNotApprovedEvent::class:
                $orderStatus = 'PENDING';
                break;
            default:
                $orderStatus = '';
        }

        // COMPLETED is a final status, always ensure we don't update to previous status due to outdated webhook for example
        if ($psCheckoutCart->getPaypalStatus() === 'COMPLETED') {
            return;
        }

        $this->commandBus->handle(new UpdatePsCheckoutSessionCommand(
            $event->getOrderPayPalId()->getValue(),
            $psCheckoutCart->getIdCart(),
            $psCheckoutCart->getPaypalFundingSource(),
            $psCheckoutCart->getPaypalIntent(),
            $orderStatus,
            $psCheckoutCart->getPaypalClientToken(),
            $psCheckoutCart->paypal_token_expire,
            $psCheckoutCart->paypal_authorization_expire,
            $psCheckoutCart->isHostedFields(),
            $psCheckoutCart->isExpressCheckout()
        ));
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     */
    public function capturePayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        // ExpressCheckout require buyer select a delivery option, we have to check if cart is ready to payment
        if ($psCheckoutCart->isExpressCheckout() && $psCheckoutCart->getPaypalFundingSource() === 'paypal') {
            return;
        }

        // @todo Always check if Cart is ready to payment before (quantities, stocks, invoice address, delivery address, delivery option...)

        // This should mainly occur for APMs
        $this->commandBus->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function updatePayPalOrderCache(PayPalOrderEvent $event)
    {
        $this->commandBus->handle(new UpdatePayPalOrderCacheCommand(
            $event->getOrderPayPalId()->getValue(),
            $event->getOrder()
        ));
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function prunePayPalOrderCache(PayPalOrderEvent $event)
    {
        $this->commandBus->handle(
            new PrunePayPalOrderCacheCommand($event->getOrderPayPalId())
        );
    }

    /**
     * @param PayPalOrderCompletedEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function updatePayPalOrderMatrice(PayPalOrderCompletedEvent $event)
    {
        $this->commandBus->handle(
            new UpdatePayPalOrderMatriceCommand($event->getOrderPayPalId()->getValue())
        );
    }

    /**
     * @param PayPalOrderCompletedEvent $event
     *
     * @return void
     *
     * @throws OrderException
     * @throws OrderStateException
     */
    public function updateOrderStatus(PayPalOrderCompletedEvent $event)
    {
        $getOrderStateConfiguration = $this->commandBus->handle(new GetOrderStateConfigurationQuery());
        $orderId = new OrderId($event->getOrder()->id);
        $currentOrderState = $getOrderStateConfiguration->getKeyById(new OrderStateId($event->getOrder()->getCurrentState()));
        $this->commandBus->handle(
            new UpdateOrderStatusCommand($orderId, $currentOrderState)
        );
    }
}
