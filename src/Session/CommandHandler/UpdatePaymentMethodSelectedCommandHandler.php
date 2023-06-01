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

namespace PrestaShop\Module\PrestashopCheckout\Session\CommandHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePaymentMethodSelectedCommand;
use PrestaShop\Module\PrestashopCheckout\Session\Event\PsCheckoutSessionUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\Session\Exception\PsCheckoutSessionException;
use PsCheckoutCart;

class UpdatePaymentMethodSelectedCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        PsCheckoutCartRepository $psCheckoutCartRepository
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    public function handle(UpdatePaymentMethodSelectedCommand $command)
    {
        try {
            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($command->getCartId()->getValue());

            if (false === $psCheckoutCart) {
                throw new PsCheckoutSessionException(sprintf('Unable to retrieve PrestaShop Checkout session #%s', $command->getCartId()->getValue()), PsCheckoutSessionException::UPDATE_FAILED);
            }

            $psCheckoutCart->id_cart = $command->getCartId()->getValue();
            $psCheckoutCart->paypal_order = $command->getOrderPayPalId()->getValue();
            $psCheckoutCart->paypal_funding = $command->getFundingSource();
            $psCheckoutCart->isHostedFields = $command->isHostedFields();
            $psCheckoutCart->isExpressCheckout = $command->isExpressCheckout();
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        } catch (Exception $exception) {
            throw new PsCheckoutSessionException(sprintf('Unable to update PrestaShop Checkout session #%s', $command->getCartId()->getValue()), PsCheckoutSessionException::UPDATE_FAILED, $exception);
        }

        $this->eventDispatcher->dispatch(
            new PsCheckoutSessionUpdatedEvent($command->getCartId()->getValue())
        );
    }
}
