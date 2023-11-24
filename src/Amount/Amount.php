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

namespace PrestaShop\Module\PrestashopCheckout\Amount;

use PrestaShop\Module\PrestashopCheckout\Amount\Exception\AmountException;

class Amount
{
    /** @var string */
    private $value;

    /** @var string */
    private $currencyCode;

    /**
     * @param string $value
     * @param string $currencyCode
     *
     * @throws AmountException
     */
    public function __construct($value, $currencyCode)
    {
        $this->value = $this->assertAmountIsValid($value);
        $this->currencyCode = $this->assertCurrencyCodeIsValid($currencyCode);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $amount
     *
     * @return string
     *
     * @throws AmountException
     */
    public function assertAmountIsValid($amount)
    {
        if (is_string($amount) && is_numeric($amount)) {
            return $amount;
        }

        throw new AmountException("Amount value $amount is not a numeric", AmountException::INVALID_AMOUNT);
    }

    /**
     * @param string $currencyCode
     *
     * @return string
     *
     * @throws AmountException
     */
    private function assertCurrencyCodeIsValid($currencyCode)
    {
        if (!in_array($currencyCode, ['AUD', 'BRL', 'CAD', 'CNY', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'SGD', 'SEK', 'CHF', 'THB', 'USD'])) {
            throw new AmountException("Currency code $currencyCode is not supported", AmountException::INVALID_CURRENCY);
        }

        if (preg_match('/^\d+\.\d+$/', $this->value) && in_array($currencyCode, ['HUF', 'JPY', 'TWD'])) {
            throw new AmountException("Currency code $currencyCode does not support decimal amount", AmountException::UNEXPECTED_DECIMAL_AMOUNT);
        }

        return $currencyCode;
    }
}
