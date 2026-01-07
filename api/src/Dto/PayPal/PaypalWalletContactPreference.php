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
 * The preference to display the contact information (buyer’s shipping email & phone number) on
 * PayPal's checkout for easy merchant-buyer communication.
 */
class PaypalWalletContactPreference
{
    /**
     * The merchant can opt out of showing buyer's contact information on PayPal checkout.
     */
    public const NO_CONTACT_INFO = 'NO_CONTACT_INFO';

    /**
     * The merchant allows buyer to add or update shipping contact information on the PayPal checkout.
     * Please ensure to use this updated information returned in shipping.email_address and shipping.
     * phone_number to contact your buyers.
     */
    public const UPDATE_CONTACT_INFO = 'UPDATE_CONTACT_INFO';

    /**
     * The buyer can only see but can not override merchant passed contact information (shipping.
     * email_address and shipping.phone_number) on PayPal checkout. NOTE: If you don't pass the contact
     * information, the behavior is the same as NO_CONTACT_INFO preference.
     */
    public const RETAIN_CONTACT_INFO = 'RETAIN_CONTACT_INFO';

    public const PREFERENCES = [
        self::NO_CONTACT_INFO,
        self::UPDATE_CONTACT_INFO,
        self::RETAIN_CONTACT_INFO,
    ];
}
