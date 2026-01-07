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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * DEPRECATED. DEPRECATED. The shipping preference: Displays the shipping address to the customer.
 * Enables the customer to choose an address on the PayPal site. Restricts the customer from changing
 * the address during the payment-approval process. .  The fields in `application_context` are now
 * available in the `experience_context` object under the `payment_source` which supports them (eg.
 * `payment_source.paypal.experience_context.shipping_preference`). Please specify this field in the
 * `experience_context` object instead of the `application_context` object.
 *
 * @deprecated
 */
class OrderApplicationContextShippingPreference
{
    /**
     * Use the customer-provided shipping address on the PayPal site.
     */
    public const GET_FROM_FILE = 'GET_FROM_FILE';

    /**
     * Redact the shipping address from the PayPal site. Recommended for digital goods.
     */
    public const NO_SHIPPING = 'NO_SHIPPING';

    /**
     * Use the merchant-provided address. The customer cannot change this address on the PayPal site.
     */
    public const SET_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';
}
