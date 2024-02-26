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

namespace PrestaShop\Module\PrestashopCheckout\Checkout\CommandHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\SaveCheckoutCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\Exception\PsCheckoutSessionException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PsCheckoutCart;

class SaveCheckoutCommandHandler
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    public function __construct(PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @param SaveCheckoutCommand $command
     *
     * @throws PsCheckoutSessionException
     */
    public function handle(SaveCheckoutCommand $command)
    {
        try {
            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($command->getCartId()->getValue());

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
            }

            $psCheckoutCart->id_cart = $command->getCartId()->getValue();
            $psCheckoutCart->paypal_order = $command->getOrderPayPalId()->getValue();
            $psCheckoutCart->paypal_funding = $command->getFundingSource();
            $psCheckoutCart->isHostedFields = $command->isHostedFields();
            $psCheckoutCart->isExpressCheckout = $command->isExpressCheckout();
            $psCheckoutCart->paypal_status = $command->getOrderPayPalStatus();
            $psCheckoutCart->paypal_intent = $command->getIntent();
            $psCheckoutCart->environment = $command->getEnvironment();
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        } catch (Exception $exception) {
            throw new PsCheckoutSessionException(sprintf('Unable to update PrestaShop Checkout session #%s', var_export($command->getCartId()->getValue(), true)), PsCheckoutSessionException::UPDATE_FAILED, $exception);
        }
    }
}
