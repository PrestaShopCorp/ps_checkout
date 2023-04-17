<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\Query;

class GetOrderPaymentQueryResult
{
    /** @var string */
    private $transactionId;

    /** @var string */
    private $orderReference;

    /** @var string */
    private $amount;

    /** @var string */
    private $paymentMethod;

    /** @var string */
    private $date;

    /**
     * @param string $transactionId
     * @param string $orderReference
     * @param string $amount
     * @param string $paymentMethod
     * @param string $date
     */
    public function __construct($transactionId, $orderReference, $amount, $paymentMethod, $date)
    {
        $this->transactionId = $transactionId;
        $this->orderReference = $orderReference;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }
}
