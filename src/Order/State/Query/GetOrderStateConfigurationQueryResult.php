<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Query;

class GetOrderStateConfigurationQueryResult
{
    /**
     * @var int
     */
    private $orderStateId;

    public function __construct($orderStateId)
    {
        $this->orderStateId = $orderStateId;
    }

    public function getOrderStateId()
    {
        return $this->orderStateId;
    }
}
