<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class SellerReceivableBreakdown
{
    /**
     * @var Amount
     */
    protected $gross_amount;

    /**
     * @var Amount|null
     */
    protected $paypal_fee;

    /**
     * @var Amount|null
     */
    protected $paypal_fee_in_receivable_currency;

    /**
     * @var Amount|null
     */
    protected $net_amount;

    /**
     * @var Amount|null
     */
    protected $receivable_amount;

    /**
     * @var ExchangeRate|null
     */
    protected $exchange_rate;

    /**
     * An array of platform or partner fees, commissions, or brokerage fees that associated with the captured payment.
     *
     * @var PlatformFee[]|null
     */
    protected $platform_fees;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->gross_amount = isset($data['gross_amount']) ? $data['gross_amount'] : null;
        $this->paypal_fee = isset($data['paypal_fee']) ? $data['paypal_fee'] : null;
        $this->paypal_fee_in_receivable_currency = isset($data['paypal_fee_in_receivable_currency']) ? $data['paypal_fee_in_receivable_currency'] : null;
        $this->net_amount = isset($data['net_amount']) ? $data['net_amount'] : null;
        $this->receivable_amount = isset($data['receivable_amount']) ? $data['receivable_amount'] : null;
        $this->exchange_rate = isset($data['exchange_rate']) ? $data['exchange_rate'] : null;
        $this->platform_fees = isset($data['platform_fees']) ? $data['platform_fees'] : null;
    }

    /**
     * Gets gross_amount.
     *
     * @return Amount
     */
    public function getGrossAmount()
    {
        return $this->gross_amount;
    }

    /**
     * Sets gross_amount.
     *
     * @param Amount $gross_amount
     *
     * @return $this
     */
    public function setGrossAmount(Amount $gross_amount)
    {
        $this->gross_amount = $gross_amount;

        return $this;
    }

    /**
     * Gets paypal_fee.
     *
     * @return Amount|null
     */
    public function getPaypalFee()
    {
        return $this->paypal_fee;
    }

    /**
     * Sets paypal_fee.
     *
     * @param Amount|null $paypal_fee
     *
     * @return $this
     */
    public function setPaypalFee(Amount $paypal_fee = null)
    {
        $this->paypal_fee = $paypal_fee;

        return $this;
    }

    /**
     * Gets paypal_fee_in_receivable_currency.
     *
     * @return Amount|null
     */
    public function getPaypalFeeInReceivableCurrency()
    {
        return $this->paypal_fee_in_receivable_currency;
    }

    /**
     * Sets paypal_fee_in_receivable_currency.
     *
     * @param Amount|null $paypal_fee_in_receivable_currency
     *
     * @return $this
     */
    public function setPaypalFeeInReceivableCurrency(Amount $paypal_fee_in_receivable_currency = null)
    {
        $this->paypal_fee_in_receivable_currency = $paypal_fee_in_receivable_currency;

        return $this;
    }

    /**
     * Gets net_amount.
     *
     * @return Amount|null
     */
    public function getNetAmount()
    {
        return $this->net_amount;
    }

    /**
     * Sets net_amount.
     *
     * @param Amount|null $net_amount
     *
     * @return $this
     */
    public function setNetAmount(Amount $net_amount = null)
    {
        $this->net_amount = $net_amount;

        return $this;
    }

    /**
     * Gets receivable_amount.
     *
     * @return Amount|null
     */
    public function getReceivableAmount()
    {
        return $this->receivable_amount;
    }

    /**
     * Sets receivable_amount.
     *
     * @param Amount|null $receivable_amount
     *
     * @return $this
     */
    public function setReceivableAmount(Amount $receivable_amount = null)
    {
        $this->receivable_amount = $receivable_amount;

        return $this;
    }

    /**
     * Gets exchange_rate.
     *
     * @return ExchangeRate|null
     */
    public function getExchangeRate()
    {
        return $this->exchange_rate;
    }

    /**
     * Sets exchange_rate.
     *
     * @param ExchangeRate|null $exchange_rate
     *
     * @return $this
     */
    public function setExchangeRate(ExchangeRate $exchange_rate = null)
    {
        $this->exchange_rate = $exchange_rate;

        return $this;
    }

    /**
     * Gets platform_fees.
     *
     * @return PlatformFee[]|null
     */
    public function getPlatformFees()
    {
        return $this->platform_fees;
    }

    /**
     * Sets platform_fees.
     *
     * @param PlatformFee[]|null $platform_fees an array of platform or partner fees, commissions, or brokerage fees that associated with the captured payment
     *
     * @return $this
     */
    public function setPlatformFees(array $platform_fees = null)
    {
        $this->platform_fees = $platform_fees;

        return $this;
    }
}
