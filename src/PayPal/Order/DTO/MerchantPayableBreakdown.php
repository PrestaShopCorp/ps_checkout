<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class MerchantPayableBreakdown
{
    /**
     * @var Amount|null
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
    protected $net_amount_in_receivable_currency;

    /**
     * An array of platform or partner fees, commissions, or brokerage fees for the refund.
     *
     * @var PlatformFee[]|null
     */
    protected $platform_fees;

    /**
     * An array of breakdown values for the net amount. Returned when the currency of the refund is different from the currency of the PayPal account where the payee holds their funds.
     *
     * @var NetAmountBreakdownItem[]|null
     */
    protected $net_amount_breakdown;

    /**
     * @var Amount|null
     */
    protected $total_refunded_amount;

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
        $this->net_amount_in_receivable_currency = isset($data['net_amount_in_receivable_currency']) ? $data['net_amount_in_receivable_currency'] : null;
        $this->platform_fees = isset($data['platform_fees']) ? $data['platform_fees'] : null;
        $this->net_amount_breakdown = isset($data['net_amount_breakdown']) ? $data['net_amount_breakdown'] : null;
        $this->total_refunded_amount = isset($data['total_refunded_amount']) ? $data['total_refunded_amount'] : null;
    }

    /**
     * Gets gross_amount.
     *
     * @return Amount|null
     */
    public function getGrossAmount()
    {
        return $this->gross_amount;
    }

    /**
     * Sets gross_amount.
     *
     * @param Amount|null $gross_amount
     *
     * @return $this
     */
    public function setGrossAmount(Amount $gross_amount = null)
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
     * Gets net_amount_in_receivable_currency.
     *
     * @return Amount|null
     */
    public function getNetAmountInReceivableCurrency()
    {
        return $this->net_amount_in_receivable_currency;
    }

    /**
     * Sets net_amount_in_receivable_currency.
     *
     * @param Amount|null $net_amount_in_receivable_currency
     *
     * @return $this
     */
    public function setNetAmountInReceivableCurrency(Amount $net_amount_in_receivable_currency = null)
    {
        $this->net_amount_in_receivable_currency = $net_amount_in_receivable_currency;

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
     * @param PlatformFee[]|null $platform_fees an array of platform or partner fees, commissions, or brokerage fees for the refund
     *
     * @return $this
     */
    public function setPlatformFees(array $platform_fees = null)
    {
        $this->platform_fees = $platform_fees;

        return $this;
    }

    /**
     * Gets net_amount_breakdown.
     *
     * @return NetAmountBreakdownItem[]|null
     */
    public function getNetAmountBreakdown()
    {
        return $this->net_amount_breakdown;
    }

    /**
     * Sets net_amount_breakdown.
     *
     * @param NetAmountBreakdownItem[]|null $net_amount_breakdown An array of breakdown values for the net amount. Returned when the currency of the refund is different from the currency of the PayPal account where the payee holds their funds.
     *
     * @return $this
     */
    public function setNetAmountBreakdown(array $net_amount_breakdown = null)
    {
        $this->net_amount_breakdown = $net_amount_breakdown;

        return $this;
    }

    /**
     * Gets total_refunded_amount.
     *
     * @return Amount|null
     */
    public function getTotalRefundedAmount()
    {
        return $this->total_refunded_amount;
    }

    /**
     * Sets total_refunded_amount.
     *
     * @param Amount|null $total_refunded_amount
     *
     * @return $this
     */
    public function setTotalRefundedAmount(Amount $total_refunded_amount = null)
    {
        $this->total_refunded_amount = $total_refunded_amount;

        return $this;
    }
}
