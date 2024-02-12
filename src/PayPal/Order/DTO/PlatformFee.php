<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PlatformFee
{
    /**
     * @var Amount
     */
    protected $amount;

    /**
     * @var Payee|null
     */
    protected $payee;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
        $this->payee = isset($data['payee']) ? $data['payee'] : null;
    }

    /**
     * Gets amount.
     *
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets amount.
     *
     * @param Amount $amount
     *
     * @return $this
     */
    public function setAmount(Money $amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets payee.
     *
     * @return Payee|null
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * Sets payee.
     *
     * @param Payee|null $payee
     *
     * @return $this
     */
    public function setPayee(Payee $payee = null)
    {
        $this->payee = $payee;

        return $this;
    }
}
