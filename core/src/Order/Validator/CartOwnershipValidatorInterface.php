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

interface CartOwnershipValidatorInterface
{
    /**
     * Checks that the given cart belongs to the customer of the current context.
     *
     * @param int $cartId
     *
     * @return bool
     */
    public function isOwnedByCurrentCustomer(int $cartId): bool;

    /**
     * Checks that the given cart is the cart of the current context, or belongs to the
     * customer of the current context.
     *
     * The context cart comes from the visitor's signed cookie, so nobody else can present it.
     * This is the rule for the controllers reachable before a customer exists: an anonymous
     * visitor starting an express checkout from the product or cart page owns a PayPal Order
     * linked to a cart with no customer, and the guest customer is only created after the
     * PayPal approval.
     *
     * @param int $cartId
     *
     * @return bool
     */
    public function isOwnedByCurrentVisitor(int $cartId): bool;
}
