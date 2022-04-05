<?php

class PayPalOrderCaptureCommand
{
    /**
     * @var int
     */
    private $cartId;
    /**
     * @var string
     */
    private $orderId;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var string
     */
    private $intent;

    public function __construct($cartId, $orderId, $merchantId, $intent)
    {
        $this->cartId = $cartId;
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->intent = $intent;
    }

    /**
     * @return int
     */
    public function getCartId()
    {
        return (int) $this->cartId;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }
}
