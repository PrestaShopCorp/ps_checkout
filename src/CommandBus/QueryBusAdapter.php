<?php

namespace PrestaShop\Module\PrestashopCheckout\CommandBus;

use PrestaShopBundle\CommandBus\MessengerCommandBus;

class QueryBusAdapter implements QueryBusInterface
{
    public function __construct(private MessengerCommandBus $bus)
    {}

    /**
     * {@inheritdoc}
     */
    public function handle($query)
    {
        return $this->bus->handle($query);
    }
}
