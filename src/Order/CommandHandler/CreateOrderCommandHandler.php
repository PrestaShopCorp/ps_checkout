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

namespace PrestaShop\Module\PrestashopCheckout\Order\CommandHandler;

use Cart;
use Exception;
use Module;
use PaymentModule;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\OrderException;

class CreateOrderCommandHandler extends AbstractOrderCommandHandler
{
    public function handle(CreateOrderCommand $command)
    {
        /** @var PaymentModule|false $paymentModule */
        $paymentModule = Module::getInstanceByName($command->getPaymentModuleName());

        if (false === $paymentModule) {
            throw new OrderException(sprintf('Unable to get "%s" module instance.', $command->getPaymentModuleName()), OrderException::MODULE_INSTANCE_NOT_FOUND);
        }

        $cart = new Cart($command->getCartId()->getValue());
        $this->setCartContext($cart);

        try {
            $paymentModule->validateOrder(
                (int) $cart->id,
                $command->getOrderStateId(),
                $cart->getOrderTotal(),
                $paymentModule->displayName,
                null,
                [],
                null,
                false,
                $cart->secure_key
            );
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', $cart->id), OrderException::PRESTASHOP_VALIDATE_ORDER, $exception);
        }

        if (!$cart->orderExists()) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', $cart->id), OrderException::PRESTASHOP_ORDER_ID_MISSING);
        }
    }
}
