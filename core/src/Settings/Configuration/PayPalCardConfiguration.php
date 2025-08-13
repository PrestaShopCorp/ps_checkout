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

namespace PsCheckout\Core\Settings\Configuration;

class PayPalCardConfiguration
{
    const DEFAULT_SUPPORTED_CARDS = [
        'MASTERCARD', 'VISA', 'AMEX', 'JCB', 'CB_NATIONALE', 'DISCOVER',
    ];

    const SUPPORTED_CARD_BRANDS_BY_COUNTRY_AND_CURRENCY = [
        'AU' => [
            'AUD' => ['MASTERCARD', 'VISA', 'AMEX'],
        ],
        'CA' => [
            'CAD' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        ],
        'FR' => [
            'EUR' => ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
            'AUD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'BRL' => ['MASTERCARD', 'VISA', 'AMEX'],
            'CAD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'CHF' => ['MASTERCARD', 'VISA', 'AMEX'],
            'CZK' => ['MASTERCARD', 'VISA', 'AMEX'],
            'DKK' => ['MASTERCARD', 'VISA', 'AMEX'],
            'GBP' => ['MASTERCARD', 'VISA', 'AMEX'],
            'HKD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'HUF' => ['MASTERCARD', 'VISA', 'AMEX'],
            'ILS' => ['MASTERCARD', 'VISA', 'AMEX'],
            'JPY' => ['MASTERCARD', 'VISA', 'AMEX'],
            'MXN' => ['MASTERCARD', 'VISA', 'AMEX'],
            'NOK' => ['MASTERCARD', 'VISA', 'AMEX'],
            'NZD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'PHP' => ['MASTERCARD', 'VISA', 'AMEX'],
            'PLN' => ['MASTERCARD', 'VISA', 'AMEX'],
            'SEK' => ['MASTERCARD', 'VISA', 'AMEX'],
            'SGD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'THB' => ['MASTERCARD', 'VISA', 'AMEX'],
            'TWD' => ['MASTERCARD', 'VISA', 'AMEX'],
            'USD' => ['MASTERCARD', 'VISA', 'AMEX'],
        ],
        'JP' => [
            'JPY' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        ],
        'US' => [
            'USD' => ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
        ],
    ];

    const SUPPORTED_CARD_BRANDS_BY_COUNTRY = [
        'AU' => ['MASTERCARD', 'VISA', 'AMEX'],
        'AT' => ['MASTERCARD', 'VISA', 'AMEX'],
        'BE' => ['MASTERCARD', 'VISA', 'AMEX'],
        'BG' => ['MASTERCARD', 'VISA', 'AMEX'],
        'CA' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        'CY' => ['MASTERCARD', 'VISA', 'AMEX'],
        'CZ' => ['MASTERCARD', 'VISA', 'AMEX'],
        'DK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'EE' => ['MASTERCARD', 'VISA', 'AMEX'],
        'FI' => ['MASTERCARD', 'VISA', 'AMEX'],
        'FR' => ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
        'DE' => ['MASTERCARD', 'VISA', 'AMEX'],
        'GR' => ['MASTERCARD', 'VISA', 'AMEX'],
        'HU' => ['MASTERCARD', 'VISA', 'AMEX'],
        'IE' => ['MASTERCARD', 'VISA', 'AMEX'],
        'IT' => ['MASTERCARD', 'VISA', 'AMEX'],
        'JP' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        'LV' => ['MASTERCARD', 'VISA', 'AMEX'],
        'LI' => ['MASTERCARD', 'VISA', 'AMEX'],
        'LT' => ['MASTERCARD', 'VISA', 'AMEX'],
        'LU' => ['MASTERCARD', 'VISA', 'AMEX'],
        'MT' => ['MASTERCARD', 'VISA', 'AMEX'],
        'MX' => ['MASTERCARD', 'VISA', 'AMEX'],
        'NL' => ['MASTERCARD', 'VISA', 'AMEX'],
        'NO' => ['MASTERCARD', 'VISA', 'AMEX'],
        'PL' => ['MASTERCARD', 'VISA', 'AMEX'],
        'PT' => ['MASTERCARD', 'VISA', 'AMEX'],
        'RO' => ['MASTERCARD', 'VISA', 'AMEX'],
        'SK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'SI' => ['MASTERCARD', 'VISA', 'AMEX'],
        'ES' => ['MASTERCARD', 'VISA', 'AMEX'],
        'SE' => ['MASTERCARD', 'VISA', 'AMEX'],
        'UK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'US' => ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
    ];

    const SUPPORTED_CARD_BRANDS_BY_CURRENCY = [
        'AUD' => ['MASTERCARD', 'VISA', 'AMEX'],
        'BRL' => ['MASTERCARD', 'VISA', 'AMEX'],
        'CAD' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        'CHF' => ['MASTERCARD', 'VISA', 'AMEX'],
        'CZK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'DKK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'EUR' => ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
        'GBP' => ['MASTERCARD', 'VISA', 'AMEX'],
        'HKD' => ['MASTERCARD', 'VISA', 'AMEX'],
        'HUF' => ['MASTERCARD', 'VISA', 'AMEX'],
        'ILS' => ['MASTERCARD', 'VISA', 'AMEX'],
        'JPY' => ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
        'MXN' => ['MASTERCARD', 'VISA', 'AMEX'],
        'NOK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'NZD' => ['MASTERCARD', 'VISA', 'AMEX'],
        'PHP' => ['MASTERCARD', 'VISA', 'AMEX'],
        'PLN' => ['MASTERCARD', 'VISA', 'AMEX'],
        'SEK' => ['MASTERCARD', 'VISA', 'AMEX'],
        'SGD' => ['MASTERCARD', 'VISA', 'AMEX'],
        'THB' => ['MASTERCARD', 'VISA', 'AMEX'],
        'TWD' => ['MASTERCARD', 'VISA', 'AMEX'],
        'USD' => ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
    ];
}
