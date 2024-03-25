<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command;

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;

class SavePayPalOrderCommand
{
    /**
     * @var array
     */
    private $order;
    /**
     * @var CartId|null
     */
    private $cartId;
    /**
     * @var string|null
     */
    private $paymentMode;
    /**
     * @var string|null
     */
    private $customerIntent;
    /**
     * @var bool|null
     */
    private $isExpressCheckout;
    /**
     * @var bool|null
     */
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
     * @return string|null
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @return string|null
     */
    public function getCustomerIntent()
    {
        return $this->customerIntent;
    }

    /**
     * @return bool|null
     */
    public function isExpressCheckout()
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return bool|null
     */
    public function isCardFields()
    {
        return $this->isCardFields;
    }
}
