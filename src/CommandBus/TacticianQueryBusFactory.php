<?php

namespace PrestaShop\Module\PrestashopCheckout\CommandBus;

use PrestaShopBundle\CommandBus\MessengerCommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class TacticianQueryBusFactory
{
    /**
     * @var array
     */
    private $queryToHandlerMap;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param array $queryToHandlerMap
     */
    public function __construct(LoggerInterface $logger, array $queryToHandlerMap)
    {
        $this->logger = $logger;
        $this->queryToHandlerMap = $queryToHandlerMap;
    }

    public function create(): TacticianQueryBusAdapter
    {
        $handlerMiddleWare = new HandleMessageMiddleware(
            new HandlersLocator($this->queryToHandlerMap),
        );

        $handlerMiddleWare->setLogger($this->logger);

        $messengerBus = new MessengerCommandBus(
            new MessageBus([$handlerMiddleWare])
        );

        return new TacticianQueryBusAdapter($messengerBus);
    }
}
