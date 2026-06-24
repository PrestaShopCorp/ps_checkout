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

namespace PsCheckout\Utility\Payload;

class PaypalAddressRequirementsUtility
{
    /**
     * Countries where postal_code is required by the PayPal Orders v2 API.
     * Source: docs/country-and-region-address-requirements.md
     *
     * @var string[]
     */
    private static $postalCodeRequired = [
        'AR', 'AU', 'AT', 'BT', 'BR', 'CA', 'C2', 'CN', 'CC', 'KM',
        'DK', 'FK', 'FO', 'FR', 'TF', 'GM', 'DE', 'GL', 'IT', 'JP',
        'KI', 'KG', 'MR', 'YT', 'MX', 'NR', 'NE', 'NL', 'NU', 'NF',
        'NO', 'PL', 'PN', 'SM', 'SG', 'SH', 'PM', 'SR', 'SJ', 'SE',
        'CH', 'TH', 'TK', 'TV', 'GB', 'US', 'UM', 'VA', 'WF', 'EH',
    ];

    /**
     * Countries where city is optional in the PayPal Orders v2 API.
     * All other countries require city.
     * Source: docs/country-and-region-address-requirements.md
     *
     * @var string[]
     */
    private static $cityOptional = ['HK', 'JP', 'SG'];

    /**
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return bool
     */
    public static function isPostalCodeRequired(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), self::$postalCodeRequired, true);
    }

    /**
     * Countries where PayPal expects the state/province ISO code (via getIsoById)
     * rather than the full name (via getNameById).
     * Source: docs/state-and-province-codes.md
     *
     * @var string[]
     */
    private static $stateIsoCodeCountries = [
        'US', 'CA', 'BR', 'IT', 'MX', 'JP', 'CN', 'C2', 'ID', 'AR',
    ];

    /**
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return bool
     */
    public static function isCityRequired(string $countryCode): bool
    {
        return !in_array(strtoupper($countryCode), self::$cityOptional, true);
    }

    /**
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     *
     * @return bool
     */
    public static function usesStateIsoCode(string $countryCode): bool
    {
        return in_array(strtoupper($countryCode), self::$stateIsoCodeCountries, true);
    }
}
