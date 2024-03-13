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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

/**
 * @see https://developer.paypal.com/docs/multiparty/checkout/advanced/#link-eligibility
 */
class AdvancedCheckoutEligibility
{
    const SUPPORTED_COUNTRIES = ['AU', 'AT', 'BE', 'BG', 'CA', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'JP', 'LV', 'LI', 'LT', 'LU', 'MT', 'MX', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'US'];

    const SUPPORTED_CURRENCIES_BY_COUNTRY = [
        'AU' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'AT' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'BE' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'BG' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'CA' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'CY' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'CZ' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'DK' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'EE' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'FI' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'FR' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'DE' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'GR' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'HU' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'IE' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'IT' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'JP' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'LV' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'LI' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'LT' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'LU' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'MT' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'MX' => ['MXN'],
        'NL' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'NO' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'PL' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'PT' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'RO' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'SK' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'SI' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'ES' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'SE' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'UK' => ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
        'US' => ['AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'],
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

    /**
     * @param string $country
     * @param string $currency
     *
     * @return bool whether the given country and currency are eligible for advanced checkout
     */
    public function isEligible($country, $currency)
    {
        return in_array($country, self::SUPPORTED_COUNTRIES, true)
            && in_array($currency, $this->getSupportedCurrenciesByCountry($country), true);
    }

    /**
     * @return array an array of countries that are supported
     */
    public function getSupportedCountries()
    {
        return self::SUPPORTED_COUNTRIES;
    }

    /**
     * @param string $country
     *
     * @return array an array of currencies that are supported for the given country
     */
    public function getSupportedCurrenciesByCountry($country)
    {
        $supportedCurrenciesByCountry = self::SUPPORTED_CURRENCIES_BY_COUNTRY;

        return isset($supportedCurrenciesByCountry[$country])
            ? $supportedCurrenciesByCountry[$country]
            : [];
    }

    /**
     * @param string $country
     *
     * @return array an array of card brands that are supported for the given country
     */
    public function getSupportedCardBrandsByCountry($country)
    {
        $supportedCardBrandsByCountry = self::SUPPORTED_CARD_BRANDS_BY_COUNTRY;

        return isset($supportedCardBrandsByCountry[$country])
            ? $supportedCardBrandsByCountry[$country]
            : [];
    }

    /**
     * @param string $currency
     *
     * @return array an array of card brands that are supported for the given currency
     */
    public function getSupportedCardBrandsByCurrency($currency)
    {
        $supportedCardBrandsByCurrency = self::SUPPORTED_CARD_BRANDS_BY_CURRENCY;

        return isset($supportedCardBrandsByCurrency[$currency])
            ? $supportedCardBrandsByCurrency[$currency]
            : [];
    }

    /**
     * @param string $country
     *
     * @return bool whether the given country has supported card brands by country and currency
     */
    public function hasSupportedCardBrandsByCountryAndCurrency($country)
    {
        $supportedCardBrandsByCountryAndCurrency = self::SUPPORTED_CARD_BRANDS_BY_COUNTRY_AND_CURRENCY;

        return isset($supportedCardBrandsByCountryAndCurrency[$country]);
    }

    /**
     * @param string $country
     * @param string $currency
     *
     * @return array an array of card brands that are supported for the given country and currency
     */
    public function getSupportedCardBrandsByCountryAndCurrency($country, $currency)
    {
        $supportedCardBrandsByCountryAndCurrency = self::SUPPORTED_CARD_BRANDS_BY_COUNTRY_AND_CURRENCY;

        return isset($supportedCardBrandsByCountryAndCurrency[$country][$currency])
            ? $supportedCardBrandsByCountryAndCurrency[$country][$currency]
            : ['MASTERCARD', 'VISA'];
    }

    /**
     * @return array an array of card brands that are supported in all countries and currencies
     */
    public function getSupportedCardBrands()
    {
        return array_values(array_unique(array_merge(...array_values(self::SUPPORTED_CARD_BRANDS_BY_COUNTRY))));
    }

    /**
     * @param string $country
     * @param string $currency
     *
     * @return array an array of card brands that are supported in the given country and for the given currency
     */
    public function getSupportedCardBrandsByContext($country, $currency)
    {
        if ($this->hasSupportedCardBrandsByCountryAndCurrency($country)) {
            return array_values(array_intersect(
                $this->getSupportedCardBrandsByCountry($country),
                $this->getSupportedCardBrandsByCurrency($currency),
                $this->getSupportedCardBrandsByCountryAndCurrency($country, $currency)
            ));
        }

        return array_values(array_intersect(
            $this->getSupportedCardBrandsByCountry($country),
            $this->getSupportedCardBrandsByCurrency($currency)
        ));
    }
}
