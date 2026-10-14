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

namespace PsCheckout\Core\Order\Validator;

use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class CartOwnershipValidator implements CartOwnershipValidatorInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CartInterface
     */
    private $cart;

    public function __construct(ContextInterface $context, CartInterface $cart)
    {
        $this->context = $context;
        $this->cart = $cart;
    }

    /**
     * {@inheritDoc}
     */
    public function isOwnedByCurrentCustomer(int $cartId): bool
    {
        $customer = $this->context->getCustomer();

        // NOTE: an anonymous visitor cannot own a cart linked to a PayPal Order
        if (!$customer || !$customer->id) {
            return false;
        }

        $cart = $this->cart->getCart($cartId);

        if (!$cart) {
            return false;
        }

        return (int) $cart->id_customer === (int) $customer->id;
    }
}
