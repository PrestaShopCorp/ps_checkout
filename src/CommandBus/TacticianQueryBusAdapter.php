<?php

namespace PrestaShop\Module\PrestashopCheckout\CommandBus;

use PrestaShopBundle\CommandBus\MessengerCommandBus;

class TacticianQueryBusAdapter implements QueryBusInterface
{
    /**
     * @var MessengerCommandBus
     */
    private $bus;

    /**
     * @param MessengerCommandBus $bus
     */
    public function __construct(MessengerCommandBus $bus)
    {
        $this->bus = $bus;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($query)
    {
        return $this->bus->handle($query);
    }
}
