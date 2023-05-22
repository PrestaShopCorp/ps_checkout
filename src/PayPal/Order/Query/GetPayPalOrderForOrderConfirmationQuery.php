<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

class GetPayPalOrderForOrderConfirmationQuery
{
    /**
     * @var PayPalOrderId
     */
    private $orderId;

    /**
     * @param string $orderId
     *
     * @throws PayPalOrderException
     */
    public function __construct($orderId)
    {
        $this->orderId = new PayPalOrderId($orderId);
    }

    /**
     * @return PayPalOrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
}
