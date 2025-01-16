<?php

namespace PrestaShop\Module\PrestashopCheckout\CommandBus;

use PrestaShopBundle\CommandBus\MessengerCommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class QueryBusFactory
{
    public function __construct(private LoggerInterface $logger, private array $queryToHandlerMap)
    {}

    public function create(): QueryBusAdapter
    {
        $handlerMiddleWare = new HandleMessageMiddleware(
            new HandlersLocator($this->queryToHandlerMap),
        );

        $handlerMiddleWare->setLogger($this->logger);

        $messengerBus = new MessengerCommandBus(
            new MessageBus([$handlerMiddleWare])
        );

        return new QueryBusAdapter($messengerBus);
    }
}
