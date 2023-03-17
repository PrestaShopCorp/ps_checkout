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

use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Event\PayPalClientTokenUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\UpdatePsCheckoutSessionCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalIdentityEventSubscriber implements EventSubscriberInterface
{
    public function __construct(UpdatePsCheckoutSessionCommandHandler $updatePsCheckoutSessionCommandHandler)
    {
        $this->updatePsCheckoutSessionCommandHandler = $updatePsCheckoutSessionCommandHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalClientTokenUpdatedEvent::NAME => 'updatePsCheckoutSession',
        ];
    }

    /**
     * @param PayPalOrderCreatedEvent $event
     *
     * @return void
     */
    public function updatePsCheckoutSession(PayPalClientTokenUpdatedEvent $event)
    {
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());
        $updatePsCheckoutSessionCommand = new UpdatePsCheckoutSessionCommand($event->getOrderPayPalId()->getValue(),$psCheckoutCart->getIdCart(),$psCheckoutCart->getPaypalFundingSource(),$psCheckoutCart->getPaypalIntent(),$psCheckoutCart->getPaypalStatus(),$event->getToken(),$psCheckoutCart->paypal_token_expire,$psCheckoutCart->paypal_authorization_expire,$psCheckoutCart->isHostedFields(),$psCheckoutCart->isExpressCheckout());
        $this->updatePsCheckoutSessionCommandHandler->handle($updatePsCheckoutSessionCommand);
    }
}
