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

use League\Tactician\Exception\MissingHandlerException;
use League\Tactician\Handler\Locator\HandlerLocator;
use Ps_checkout;

class TacticianContainerLocator implements HandlerLocator
{
    /**
     * @var Ps_checkout
     */
    protected $module;

    /**
     * @var array
     */
    protected $commandNameToHandlerMap = [];

    /**
     * @param Ps_checkout $module
     * @param array $commandNameToHandlerMap
     */
    public function __construct(Ps_checkout $module, array $commandNameToHandlerMap = [])
    {
        $this->module = $module;
        $this->addHandlers($commandNameToHandlerMap);
    }

    /**
     * Bind a handler instance to receive all commands with a certain class
     *
     * @param string $handler Handler to receive class
     * @param string $commandName Can be a class name or name of a NamedCommand
     */
    public function addHandler($handler, $commandName)
    {
        $this->commandNameToHandlerMap[$commandName] = $handler;
    }

    /**
     * Allows you to add multiple handlers at once.
     *
     * The map should be an array in the format of:
     *  [
     *      'AddTaskCommand'      => 'AddTaskCommandHandler',
     *      'CompleteTaskCommand' => 'CompleteTaskCommandHandler',
     *  ]
     *
     * @param array $commandNameToHandlerMap
     */
    public function addHandlers(array $commandNameToHandlerMap)
    {
        foreach ($commandNameToHandlerMap as $commandName => $handler) {
            $this->addHandler($handler, $commandName);
        }
    }

    /**
     * Retrieves the handler for a specified command
     *
     * @param string $commandName
     *
     * @return object
     *
     * @throws MissingHandlerException
     */
    public function getHandlerForCommand($commandName)
    {
        if (!isset($this->commandNameToHandlerMap[$commandName])) {
            throw MissingHandlerException::forCommand($commandName);
        }

        $serviceId = $this->commandNameToHandlerMap[$commandName];

        return $this->module->getService($serviceId);
    }
}
