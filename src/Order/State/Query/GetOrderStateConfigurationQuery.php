<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Query;

class GetOrderStateConfigurationQuery
{
    /**
     * @var int
     */
    private $orderStateConfigurationId;

    /**
     * @param int $orderStateConfigurationId
     */
    public function __construct($orderStateConfigurationId)
    {
        $this->orderStateConfigurationId = $orderStateConfigurationId;
    }

    /**
     * @return int
     */
    public function getOrderStateConfigurationId()
    {
        return $this->orderStateConfigurationId;
    }
}
