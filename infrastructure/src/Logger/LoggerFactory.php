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

namespace PsCheckout\Infrastructure\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use PsCheckout\Core\Exception\PsCheckoutException;
use Psr\Log\LoggerInterface;

/**
 * Class responsible for create Logger instance.
 */
class LoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var HandlerInterface
     */
    private $loggerHandler;

    /**
     * @param string $name
     * @param HandlerInterface $loggerHandler
     *
     * @throws PsCheckoutException
     */
    public function __construct(string $name, HandlerInterface $loggerHandler)
    {
        $this->assertNameIsValid($name);
        $this->name = $name;
        $this->loggerHandler = $loggerHandler;
    }

    /**
     * @return LoggerInterface
     */
    public function build(): LoggerInterface
    {
        return new Logger(
            $this->name,
            [
                $this->loggerHandler,
            ],
            [
                new ProcessIdProcessor(),
                new PsrLogMessageProcessor(),
            ]
        );
    }

    /**
     * @param string $name
     *
     * @throws PsCheckoutException
     */
    private function assertNameIsValid(string $name)
    {
        if (empty($name)) {
            throw new PsCheckoutException('Logger name cannot be empty.');
        }

        if (!is_string($name)) {
            throw new PsCheckoutException('Logger name should be a string.');
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $name)) {
            throw new PsCheckoutException('Logger name is invalid.');
        }
    }
}
