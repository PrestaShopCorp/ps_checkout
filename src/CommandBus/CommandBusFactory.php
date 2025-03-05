<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\CommandBus;

use PrestaShopBundle\CommandBus\MessengerCommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

class CommandBusFactory
{
    public function __construct(private LoggerInterface $logger, private array $commandToHandlerMap)
    {
    }

    public function create(): CommandBusAdapter
    {
        $handlerMiddleWare = new HandleMessageMiddleware(
            new HandlersLocator($this->commandToHandlerMap),
        );

        $handlerMiddleWare->setLogger($this->logger);

        $messengerBus = new MessengerCommandBus(
            new MessageBus([$handlerMiddleWare])
        );

        return new CommandBusAdapter($messengerBus);
    }
}
