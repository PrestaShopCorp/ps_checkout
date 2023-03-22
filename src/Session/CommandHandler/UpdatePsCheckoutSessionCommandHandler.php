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
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\Session\Event\PsCheckoutSessionUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\Session\Exception\PsCheckoutSessionException;
use PsCheckoutCart;

class UpdatePsCheckoutSessionCommandHandler
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

    /**
     * @param UpdatePsCheckoutSessionCommand $updatePsCheckoutSessionCommand
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws CartException
     * @throws PsCheckoutSessionException
     */
    public function handle(UpdatePsCheckoutSessionCommand $updatePsCheckoutSessionCommand)
    {
        try {
            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($updatePsCheckoutSessionCommand->getCartId()->getValue());

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = $updatePsCheckoutSessionCommand->getCartId()->getValue();
                $psCheckoutCart->paypal_intent = $updatePsCheckoutSessionCommand->getPaypalIntent();
                $psCheckoutCart->paypal_order = $updatePsCheckoutSessionCommand->getPayPalOrderId()->getValue();
                $psCheckoutCart->paypal_status = $updatePsCheckoutSessionCommand->getPaypalStatus();
                $this->psCheckoutCartRepository->save($psCheckoutCart);
            } else {
                $psCheckoutCart->paypal_order = $updatePsCheckoutSessionCommand->getPayPalOrderId()->getValue();
                $psCheckoutCart->paypal_status = $updatePsCheckoutSessionCommand->getPaypalStatus();
                $this->psCheckoutCartRepository->save($psCheckoutCart);
            }
        } catch (Exception $exception) {
            throw new PsCheckoutSessionException(sprintf('Unable to update PrestaShop Checkout session #%s', $updatePsCheckoutSessionCommand->getPayPalOrderId()->getValue()), PsCheckoutSessionException::UPDATE_FAILED, $exception);
        }

        $this->eventDispatcher->dispatch(
            new PsCheckoutSessionUpdatedEvent($updatePsCheckoutSessionCommand->getCartId()->getValue())
        );
    }
}
