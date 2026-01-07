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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\Money;

/**
 * The net amount. Returned when the currency of the refund is different from the currency of the
 * PayPal account where the merchant holds their funds.
 */
class NetAmountBreakdownItem
{
    /**
     * @var Money|null
     */
    private $payableAmount;

    /**
     * @var Money|null
     */
    private $convertedAmount;

    /**
     * @var ExchangeRate|null
     */
    private $exchangeRate;

    /**
     * Returns Payable Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getPayableAmount(): ?Money
    {
        return $this->payableAmount;
    }

    /**
     * Sets Payable Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps payable_amount
     */
    public function setPayableAmount(?Money $payableAmount): void
    {
        $this->payableAmount = $payableAmount;
    }

    /**
     * Returns Converted Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getConvertedAmount(): ?Money
    {
        return $this->convertedAmount;
    }

    /**
     * Sets Converted Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps converted_amount
     */
    public function setConvertedAmount(?Money $convertedAmount): void
    {
        $this->convertedAmount = $convertedAmount;
    }

    /**
     * Returns Exchange Rate.
     * The exchange rate that determines the amount to convert from one currency to another currency.
     */
    public function getExchangeRate(): ?ExchangeRate
    {
        return $this->exchangeRate;
    }

    /**
     * Sets Exchange Rate.
     * The exchange rate that determines the amount to convert from one currency to another currency.
     *
     * @maps exchange_rate
     */
    public function setExchangeRate(?ExchangeRate $exchangeRate): void
    {
        $this->exchangeRate = $exchangeRate;
    }
}
