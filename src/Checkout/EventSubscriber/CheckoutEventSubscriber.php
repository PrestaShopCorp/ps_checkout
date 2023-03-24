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

namespace PrestaShop\Module\PrestashopCheckout\Checkout\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Checkout\Event\CheckoutCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\GetPayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\GetPayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\Session\CommandHandler\UpdatePsCheckoutSessionCommandHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var GetPayPalOrderCommandHandler
     */
    private $getPayPalOrderCommandHandler;

    /**
     * @var UpdatePsCheckoutSessionCommandHandler
     */
    private $updatePsCheckoutSessionCommandHandler;

    /**
     * @param GetPayPalOrderCommandHandler $getPayPalOrderCommandHandler
     * @param UpdatePsCheckoutSessionCommandHandler $updatePsCheckoutSessionCommandHandler
     */
    public function __construct(
        GetPayPalOrderCommandHandler $getPayPalOrderCommandHandler,
        UpdatePsCheckoutSessionCommandHandler $updatePsCheckoutSessionCommandHandler
    ) {
        $this->getPayPalOrderCommandHandler = $getPayPalOrderCommandHandler;
        $this->updatePsCheckoutSessionCommandHandler = $updatePsCheckoutSessionCommandHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutCompletedEvent::class => 'onCheckoutCompleted',
        ];
    }

    /**
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     */
    public function onCheckoutCompleted(CheckoutCompletedEvent $event)
    {
        $this->getPayPalOrderCommandHandler->handle(
            new GetPayPalOrderCommand($event->getPayPalOrderId()->getValue())
        );

        // TODO : remplir les paramètres en s'inspirant de updatePayPalOrder dans PayPalOrderEventSubscriber
//        $this->updatePsCheckoutSessionCommandHandler->handle(
//            new UpdatePsCheckoutSessionCommand(
//                $event->getPayPalOrderId()->getValue(),
//                '',
//                $event->getFundingSource(),
//                '',
//                '',
//                '',
//                '',
//                '',
//                $event->isHostedFields(),
//                $event->isExpressCheckout()
//            )
//        );

        // Update data on pscheckout_cart table
        // Check if Cart is still valid
        // Check if PayPal Order is ready to capture
        // Try to capture
        // Create an Order on PrestaShop
    }
}
