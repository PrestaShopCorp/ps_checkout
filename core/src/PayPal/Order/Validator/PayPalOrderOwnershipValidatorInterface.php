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

namespace PsCheckout\Core\PayPal\Order\Validator;

interface PayPalOrderOwnershipValidatorInterface
{
    /**
     * Checks that the PayPal Order exists and that its cart is the cart of the current context
     * or belongs to the customer of the current context, see CartOwnershipValidatorInterface::isOwnedByCurrentVisitor().
     *
     * This is the rule for the front controllers reachable before a customer exists (check, cancel):
     * an anonymous visitor starting an express checkout owns a PayPal Order linked to a cart without customer.
     *
     * An unknown PayPal Order is refused like a foreign one, so that the caller can answer the same way
     * in both cases and the response does not tell whether the order exists.
     *
     * @param string $payPalOrderId
     *
     * @return bool
     */
    public function isOwnedByCurrentVisitor(string $payPalOrderId): bool;
}
