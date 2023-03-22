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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

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
    public function __construct(EventDispatcherInterface $eventDispatcher, PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    public function handle(UpdatePsCheckoutSessionCommand $updatePsCheckoutSessionCommand)
    {
        try {
            /** @var \PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($updatePsCheckoutSessionCommand->getOrderId()->getValue());
            if (false === $psCheckoutCart) {
                $psCheckoutCart = new \PsCheckoutCart();
                $psCheckoutCart->id_cart = $updatePsCheckoutSessionCommand->getIdCart()->getValue();
                $psCheckoutCart->paypal_intent = $updatePsCheckoutSessionCommand->getPaypalIntent();
                $psCheckoutCart->paypal_order = $updatePsCheckoutSessionCommand->getOrderId()->getValue();
                $psCheckoutCart->paypal_status = $updatePsCheckoutSessionCommand->getPaypalStatus();
                $this->psCheckoutCartRepository->save($psCheckoutCart);
            } else {
                $psCheckoutCart->paypal_order = $updatePsCheckoutSessionCommand->getOrderId()->getValue();
                $psCheckoutCart->paypal_status = $updatePsCheckoutSessionCommand->getPaypalStatus();
                $this->psCheckoutCartRepository->save($psCheckoutCart);
            }
            // Update an Aggregate or dispatch an Event with $transactionIdentifier
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to capture PayPal Order #%d', $updatePsCheckoutSessionCommand->getOrderId()->getValue()), PayPalOrderException::SESSION_EXCEPTION, $exception);
        }

        $this->eventDispatcher->dispatch(
            new PayPalOrderCompletedEvent($updatePsCheckoutSessionCommand->getOrderId()->getValue())
        );
    }
}
