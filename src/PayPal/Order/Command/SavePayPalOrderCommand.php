<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command;

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;

class SavePayPalOrderCommand
{
    /**
     * @var array
     */
    private $order;
    private $cartId;
    private $paymentMode;
    private $customerIntent;
    private $isExpressCheckout;
    private $isCardFields;

    /**
     * @param array $order
     */
    public function __construct($order, CartId $cartId = null, $paymentMode = null, $customerIntent = null, $isExpressCheckout = null, $isCardFields = null)
    {
        $this->order = $order;
        $this->cartId = $cartId;
        $this->paymentMode = $paymentMode;
        $this->customerIntent = $customerIntent;
        $this->isExpressCheckout = $isExpressCheckout;
        $this->isCardFields = $isCardFields;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return CartId|null
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return mixed
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @return mixed
     */
    public function getCustomerIntent()
    {
        return $this->customerIntent;
    }

    /**
     * @return mixed
     */
    public function isExpressCheckout()
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return mixed
     */
    public function isCardFields()
    {
        return $this->isCardFields;
    }
}
