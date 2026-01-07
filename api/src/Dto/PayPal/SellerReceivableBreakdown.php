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
 * The detailed breakdown of the capture activity. This is not available for transactions that are in
 * pending state.
 */
class SellerReceivableBreakdown
{
    /**
     * @var Money
     */
    private $grossAmount;

    /**
     * @var Money|null
     */
    private $paypalFee;

    /**
     * @var Money|null
     */
    private $paypalFeeInReceivableCurrency;

    /**
     * @var Money|null
     */
    private $netAmount;

    /**
     * @var Money|null
     */
    private $receivableAmount;

    /**
     * @var ExchangeRate|null
     */
    private $exchangeRate;

    /**
     * @var PlatformFee[]|null
     */
    private $platformFees;

    /**
     * @param Money $grossAmount
     */
    public function __construct(Money $grossAmount)
    {
        $this->grossAmount = $grossAmount;
    }

    /**
     * Returns Gross Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getGrossAmount(): Money
    {
        return $this->grossAmount;
    }

    /**
     * Sets Gross Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @required
     * @maps gross_amount
     * @return self
     */
    public function setGrossAmount(Money $grossAmount): self
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    /**
     * Returns Paypal Fee.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getPaypalFee(): ?Money
    {
        return $this->paypalFee;
    }

    /**
     * Sets Paypal Fee.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps paypal_fee
     * @return self
     */
    public function setPaypalFee(?Money $paypalFee): self
    {
        $this->paypalFee = $paypalFee;

        return $this;
    }

    /**
     * Returns Paypal Fee in Receivable Currency.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getPaypalFeeInReceivableCurrency(): ?Money
    {
        return $this->paypalFeeInReceivableCurrency;
    }

    /**
     * Sets Paypal Fee in Receivable Currency.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps paypal_fee_in_receivable_currency
     * @return self
     */
    public function setPaypalFeeInReceivableCurrency(?Money $paypalFeeInReceivableCurrency): self
    {
        $this->paypalFeeInReceivableCurrency = $paypalFeeInReceivableCurrency;

        return $this;
    }

    /**
     * Returns Net Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getNetAmount(): ?Money
    {
        return $this->netAmount;
    }

    /**
     * Sets Net Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps net_amount
     * @return self
     */
    public function setNetAmount(?Money $netAmount): self
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    /**
     * Returns Receivable Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getReceivableAmount(): ?Money
    {
        return $this->receivableAmount;
    }

    /**
     * Sets Receivable Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps receivable_amount
     * @return self
     */
    public function setReceivableAmount(?Money $receivableAmount): self
    {
        $this->receivableAmount = $receivableAmount;

        return $this;
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
     * @return self
     */
    public function setExchangeRate(?ExchangeRate $exchangeRate): self
    {
        $this->exchangeRate = $exchangeRate;

        return $this;
    }

    /**
     * Returns Platform Fees.
     * An array of platform or partner fees, commissions, or brokerage fees that associated with the
     * captured payment.
     *
     * @return PlatformFee[]|null
     */
    public function getPlatformFees(): ?array
    {
        return $this->platformFees;
    }

    /**
     * Sets Platform Fees.
     * An array of platform or partner fees, commissions, or brokerage fees that associated with the
     * captured payment.
     *
     * @maps platform_fees
     *
     * @param PlatformFee[]|null $platformFees
     * @return self
     */
    public function setPlatformFees(?array $platformFees): self
    {
        $this->platformFees = $platformFees;

        return $this;
    }
}
