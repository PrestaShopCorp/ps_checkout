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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * @see https://developer.paypal.com/api/rest/reference/currency-codes/
 */
class PayPalCurrencyProvider
{
    const CURRENCIES = [
        ['iso_code' => 'AUD', 'isDecimalSupported' => true],
        ['iso_code' => 'BRL', 'isDecimalSupported' => true],
        ['iso_code' => 'CAD', 'isDecimalSupported' => true],
        ['iso_code' => 'CNY', 'isDecimalSupported' => true],
        ['iso_code' => 'CZK', 'isDecimalSupported' => true],
        ['iso_code' => 'DKK', 'isDecimalSupported' => true],
        ['iso_code' => 'EUR', 'isDecimalSupported' => true],
        ['iso_code' => 'HKD', 'isDecimalSupported' => true],
        ['iso_code' => 'HUF', 'isDecimalSupported' => false],
        ['iso_code' => 'ILS', 'isDecimalSupported' => true],
        ['iso_code' => 'JPY', 'isDecimalSupported' => false],
        ['iso_code' => 'MYR', 'isDecimalSupported' => true],
        ['iso_code' => 'MXN', 'isDecimalSupported' => true],
        ['iso_code' => 'TWD', 'isDecimalSupported' => false],
        ['iso_code' => 'NZD', 'isDecimalSupported' => true],
        ['iso_code' => 'NOK', 'isDecimalSupported' => true],
        ['iso_code' => 'PHP', 'isDecimalSupported' => true],
        ['iso_code' => 'PLN', 'isDecimalSupported' => true],
        ['iso_code' => 'GBP', 'isDecimalSupported' => true],
        ['iso_code' => 'RUB', 'isDecimalSupported' => true],
        ['iso_code' => 'SGD', 'isDecimalSupported' => true],
        ['iso_code' => 'SEK', 'isDecimalSupported' => true],
        ['iso_code' => 'CHF', 'isDecimalSupported' => true],
        ['iso_code' => 'THB', 'isDecimalSupported' => true],
        ['iso_code' => 'USD', 'isDecimalSupported' => true],
    ];

    /**
     * @return string[]
     */
    public function getSupportedCurrencyCode()
    {
        return array_column(static::CURRENCIES, 'iso_code');
    }

    /**
     * @param string $code
     *
     * @return PayPalCurrency
     *
     * @throws PsCheckoutException
     */
    public function getByCode($code)
    {
        foreach (static::CURRENCIES as $currency) {
            if (0 === strcasecmp($code, $currency['iso_code'])) {
                return PayPalCurrency::fromArray($currency);
            }
        }

        throw new PsCheckoutException(sprintf('Unsupported currency code, given %s', var_export($code, true)), PsCheckoutException::PSCHECKOUT_CURRENCY_CODE_INVALID);
    }
}
