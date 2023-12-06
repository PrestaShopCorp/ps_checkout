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

namespace PrestaShop\Module\PrestashopCheckout\Currency\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Currency\Exception\CurrencyException;

class CurrencyCode
{
    const CURRENCY_CODE_AVAILABLE = [
        'AUD',
        'BRL',
        'CAD',
        'CNY',
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'JPY',
        'MYR',
        'MXN',
        'TWD',
        'NZD',
        'NOK',
        'PHP',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'THB',
        'USD',
    ];

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @param string $currencyCode
     *
     * @throws CurrencyException
     */
    public function __construct($currencyCode)
    {
        $this->currencyCode = $this->assertCurrencyCodeIsValid($currencyCode);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->currencyCode;
    }

    /**
     * @param $currencyCode
     *
     * @return string
     *
     * @throws CurrencyException
     */
    public function assertCurrencyCodeIsValid($currencyCode)
    {
        if (!is_string($currencyCode)) {
            throw new CurrencyException(sprintf('CODE is not a string (%s)', gettype($currencyCode)), CurrencyException::WRONG_TYPE_CODE);
        }
        if (!in_array($currencyCode, self::CURRENCY_CODE_AVAILABLE)) {
            throw new CurrencyException("Invalid code ($currencyCode)", CurrencyException::INVALID_CODE);
        }

        return $currencyCode;
    }
}
