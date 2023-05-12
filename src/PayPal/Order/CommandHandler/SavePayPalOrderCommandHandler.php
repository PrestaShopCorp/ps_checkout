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
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderSavedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

class SavePayPalOrderCommandHandler
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

    public function handle(SavePayPalOrderCommand $savePayPalOrderCommand)
    {
        try {
            /** @var \PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($savePayPalOrderCommand->getOrderPayPalId()->getValue());

            $psCheckoutCart->paypal_order = $savePayPalOrderCommand->getOrderPayPalId()->getValue();
            $psCheckoutCart->paypal_status = $savePayPalOrderCommand->getOrderPaypalStatus();
            $this->psCheckoutCartRepository->save($psCheckoutCart);
            // Update an Aggregate or dispatch an Event with $transactionIdentifier
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PrestaShop cart #%d', $savePayPalOrderCommand->getOrderPayPalId()->getValue()), PayPalOrderException::SESSION_EXCEPTION, $exception);
        }

        $this->eventDispatcher->dispatch(
            new PayPalOrderSavedEvent(
                $savePayPalOrderCommand->getOrderPayPalId()->getValue(),
            )
        );
    }
}
