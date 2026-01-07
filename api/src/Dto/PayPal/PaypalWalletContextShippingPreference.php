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
 * The location from which the shipping address is derived.
 */
class PaypalWalletContextShippingPreference
{
    /**
     * Get the customer-provided shipping address on the PayPal site.
     */
    public const GET_FROM_FILE = 'GET_FROM_FILE';

    /**
     * Removes the shipping address information from the API response and the Paypal site. However, the
     * shipping.phone_number and shipping.email_address fields will still be returned to allow for digital
     * goods delivery.
     */
    public const NO_SHIPPING = 'NO_SHIPPING';

    /**
     * Get the merchant-provided address. The customer cannot change this address on the PayPal site. If
     * merchant does not pass an address, customer can choose the address on PayPal pages.
     */
    public const SET_PROVIDED_ADDRESS = 'SET_PROVIDED_ADDRESS';

    public const PREFERENCES = [
        self::GET_FROM_FILE,
        self::NO_SHIPPING,
        self::SET_PROVIDED_ADDRESS,
    ];
}
