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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Identity\EventSubscriber;

use DateTime;
use Exception;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Event\PayPalClientTokenUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\Session\CommandHandler\UpdatePsCheckoutSessionCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Session\Exception\PsCheckoutSessionException;
use PrestaShopException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalIdentityEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UpdatePsCheckoutSessionCommandHandler
     */
    private $updatePsCheckoutSessionCommandHandler;

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
            PayPalClientTokenUpdatedEvent::class => 'updatePsCheckoutSession',
        ];
    }

    /**
     * @param PayPalClientTokenUpdatedEvent $event
     *
     * @return void
     *
     * @throws PrestaShopException
     * @throws CartException
     * @throws PayPalOrderException
     * @throws PsCheckoutSessionException
     * @throws Exception
     */
    public function updatePsCheckoutSession(PayPalClientTokenUpdatedEvent $event)
    {
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId($event->getCartId()->getValue());
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
}
