<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\State\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfiguration;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;

class GetOrderStateConfigurationQueryHandler
{
    public function handle(GetOrderStateConfigurationQuery $query)
    {
        $reflection = new \ReflectionClass(OrderStateConfiguration::class);

        $configName = '';
        $constants = $reflection->getConstants();
        foreach ($constants as $name => $value) {
            if ($value === $query->getOrderStateConfigurationId()) {
                $configName = $name;
                break;
            }
        }

        // @TODO : Gérer le cas pour récupérer correctement en cas de multishop
        $orderStateId = \Db::getInstance()->getValue(
            'SELECT value
            FROM `' . _DB_PREFIX_ . 'configuration`
            WHERE `name` = "' . $configName . '";'
        );

        return new GetOrderStateConfigurationQueryResult($orderStateId);
    }
}
