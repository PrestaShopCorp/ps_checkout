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
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Matrice\Command\UpdateOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopException;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    public function __construct(PsCheckoutCartRepository $psCheckoutCartRepository, Ps_checkout $module)
    {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->commandBus = $module->getService('ps_checkout.bus.command');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderCreatedEvent::class => 'updateOrderMatrice',
        ];
    }

    /**
     * @param OrderCreatedEvent $event
     *
     * @return void
     *
     * @throws OrderException
     * @throws PayPalOrderException
     * @throws PrestaShopException
     */
    public function updateOrderMatrice(OrderCreatedEvent $event)
    {
        $cartId = $event->getCartId()->getValue();
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($cartId);

        $this->commandBus->handle(new UpdateOrderMatriceCommand(
            $event->getOrderId()->getValue(),
            $psCheckoutCart->getPaypalOrderId()
        ));
    }
}
