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

use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\CreateOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\QueryHandler\GetOrderQueryHandler;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\Session\CommandHandler\UpdatePsCheckoutSessionCommandHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UpdatePsCheckoutSessionCommandHandler
     */
    private $updatePsCheckoutSessionCommandHandler;

    /**
     * @var GetOrderQueryHandler
     */
    private $getPayPalOrderQueryHandler;

    /**
     * @var CreateOrderCommandHandler;
     */
    private $createOrderCommandHandler;

    const ORDER_STATUS_PENDING = 'PENDING';
    const ORDER_STATUS_DENIED = 'DENIED';
    const ORDER_STATUS_VOIDED = 'VOIDED';
    const ORDER_STATUS_COMPLETED = 'COMPLETED';
    const ORDER_STATUS_DECLINED = 'DECLINED';
    const ORDER_STATUS_REFUNDED = 'REFUNDED';

    public function __construct(UpdatePsCheckoutSessionCommandHandler $updatePsCheckoutSessionCommandHandler, GetOrderQueryHandler $getPayPalOrderQueryHandler, CreateOrderCommandHandler $createOrderCommandHandler)
    {
        $this->updatePsCheckoutSessionCommandHandler = $updatePsCheckoutSessionCommandHandler;
        $this->getPayPalOrderQueryHandler = $getPayPalOrderQueryHandler;
        $this->createOrderCommandHandler = $createOrderCommandHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => 'onPayPalOrderCreated',
            PayPalOrderApprovedEvent::class => 'onPayPalOrderApproved',
            PayPalOrderCompletedEvent::class => 'onPayPalOrderCompleted',
        ];
    }

    /**
     * @param PayPalOrderCreatedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderCreated(PayPalOrderCreatedEvent $event)
    {
        // Update data on pscheckout_cart table
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalCaptureId()->getValue());
        $this->updatePsCheckoutSessionCommandHandler->handle(
            new UpdatePsCheckoutSessionCommand(
                $psCheckoutCart->getPaypalOrderId(),
                $event->getCartId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource(),
                $psCheckoutCart->getPaypalIntent(),
                $psCheckoutCart->getPaypalStatus(),
                $event->getToken(),
                (new DateTime())->setTimestamp($event->getCreatedAt())->modify("+{$event->getExpireIn()} seconds")->format('Y-m-d H:i:s'),
                $psCheckoutCart->paypal_authorization_expire,
                $psCheckoutCart->isHostedFields(),
                $psCheckoutCart->isExpressCheckout()
            )
        );
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderApproved(PayPalOrderApprovedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if Cart is still valid
        // Check if an Order on PrestaShop already exist
        // Create an Order on PrestaShop if needed
        // Proceed to Capture
    }

    /**
     * @param PayPalOrderCompletedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderCompleted(PayPalOrderCompletedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Update data on pscheckout_cart table
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalCaptureId()->getValue());
        $this->updatePsCheckoutSessionCommandHandler->handle(
            new UpdatePsCheckoutSessionCommand(
                $psCheckoutCart->getPaypalOrderId(),
                $event->getCartId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource(),
                $psCheckoutCart->getPaypalIntent(),
                $psCheckoutCart->getPaypalStatus(),
                $event->getToken(),
                (new DateTime())->setTimestamp($event->getCreatedAt())->modify("+{$event->getExpireIn()} seconds")->format('Y-m-d H:i:s'),
                $psCheckoutCart->paypal_authorization_expire,
                $psCheckoutCart->isHostedFields(),
                $psCheckoutCart->isExpressCheckout()
            )
        );
        // Check if an Order on PrestaShop already exist
        // Check if the OrderState of Order on PrestaShop need to be updated
    }
}
