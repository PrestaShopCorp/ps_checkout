<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\State\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateQueryResult;

class GetOrderStateQueryHandler
{
    public function handle(GetOrderStateQuery $query)
    {
        return new GetOrderStateQueryResult(\Configuration::getGlobalValue($query->getOrderState()));
    }
}
