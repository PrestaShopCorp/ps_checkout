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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

class FundingSourceEligibilityConstraint
{
    /**
     * Get eligible countries to PayPal funding sources
     *
     * @param string $fundingSourceName
     *
     * @return array
     */
    public function getCountries($fundingSourceName)
    {
        $countries = [
            'bancontact' => ['BE'],
            'blik' => ['PL'],
            'eps' => ['AT'],
            'giropay' => ['DE'],
            'ideal' => ['NL'],
            'mybank' => ['IT'],
            'p24' => ['PL'],
            'paylater' => ['FR', 'GB', 'US', 'ES', 'IT'],
            'google_pay' => ['AU', 'AT', 'BE', 'BG', 'CA', 'CN', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MK', 'MT', 'NL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'US'],
            'apple_pay' => ['AU', 'AT', 'BE', 'BG', 'CA', 'CN', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'US'],
        ];

        return $countries[$fundingSourceName];
    }

    /**
     * Get eligible currencies for PayPal funding sources
     *
     * @param string $fundingSourceName
     *
     * @return array
     */
    public function getCurrencies($fundingSourceName)
    {
        $currencies = [
            'google_pay' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            'apple_pay' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        ];

        return $currencies[$fundingSourceName];
    }
}
