<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query;

class GetPayPalOrderForOrderConfirmationQueryResult
{
    /**
     * @var array
     */
    private $order;

    /**
     * @param array $order
     */
    public function __construct(array $order)
    {
        $this->order = $order;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }
}
