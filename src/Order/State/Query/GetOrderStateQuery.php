<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Query;

class GetOrderStateQuery
{
    /**
     * @var string Const in OrderStateConfiguration
     */
    private $orderState;

    /**
     * @param string $orderState
     */
    public function __construct($orderState)
    {
        $this->orderState = $orderState;
    }

    /**
     * @return string
     */
    public function getOrderState()
    {
        return $this->orderState;
    }
}
