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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\UpdateOrderStatusHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureReversedEvent;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UpdateOrderStatusHandler
     */
    private $updateOrderStatusCommandHandler;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @param UpdateOrderStatusHandler $updateOrderStatusCommandHandler
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     */
    public function __construct(UpdateOrderStatusHandler $updateOrderStatusCommandHandler, PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->updateOrderStatusCommandHandler = $updateOrderStatusCommandHandler;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureCompletedEvent::class => 'updateOrderStatus',
            PayPalCaptureDeniedEvent::class => 'updateOrderStatus',
            PayPalCapturePendingEvent::class => 'updateOrderStatus',
            PayPalCaptureRefundedEvent::class => 'updateOrderStatus',
            PayPalCaptureReversedEvent::class => 'updateOrderStatus',
        ];
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     */
    public function updatePayPalCapture($event)
    {
        // @todo We don't have a dedicated table for capture data storage in database yet
    }

    /**
     * @param PayPalCaptureEvent $event
     *
     * @return void
     */
    public function updateOrderStatus(PayPalCaptureEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalOrderId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getPayPalOrderId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $orderId = null;

        // Order::getIdByCartId() is available since PrestaShop 1.7.1.0
        if (method_exists(\Order::class, 'getIdByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getIdByCartId($psCheckoutCart->getIdCart());
        }

        // Order::getIdByCartId() is available before PrestaShop 1.7.1.0, removed since PrestaShop 8.0.0
        if (method_exists(\Order::class, 'getOrderByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getOrderByCartId($psCheckoutCart->getIdCart());
        }

        if (!$orderId) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.', PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        $order = new \Order($orderId);
        $currentOrderStateId = (int) $order->getCurrentState();
        $newOrderStateId = (int) $this->getNewState($event, $currentOrderStateId, $psCheckoutCart->getPaypalFundingSource());

        // Prevent duplicate state entry
        if ($currentOrderStateId !== $newOrderStateId
            && false === (bool) $order->hasBeenPaid()
            && false === (bool) $order->hasBeenShipped()
            && false === (bool) $order->hasBeenDelivered()
            && false === (bool) $order->isInPreparation()
        ) {
            $this->updateOrderStatusCommandHandler->handle(new UpdateOrderStatusCommand($orderId, $newOrderStateId));
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

        if (PayPalCaptureReversedEvent::class === $eventClass) {
            return (int) \Configuration::getGlobalValue('PS_OS_CANCELED');
        }

        if (PayPalCaptureCompletedEvent::class === $eventClass) {
            return $this->getPaidStatusId($currentOrderStateId);
        }

        if (PayPalCaptureDeniedEvent::class === $eventClass) {
            return (int) \Configuration::getGlobalValue('PS_OS_ERROR');
        }

        if (PayPalCaptureRefundedEvent::class === $eventClass) {
            return (int) \Configuration::getGlobalValue('PS_OS_REFUND');
        }

        return $this->getPendingStatusId($fundingSource);
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
