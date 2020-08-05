<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class responsible for create Logger instance.
 */
class LoggerFactory
{
    /**
     * @var LoggerDirectory
     */
    private $loggerDirectory;

    /**
     * @var \Module
     */
    private $module;

    /**
     * @param LoggerDirectory $loggerDirectory
     * @param \Module $module
     */
    public function __construct(LoggerDirectory $loggerDirectory, \Module $module)
    {
        $this->loggerDirectory = $loggerDirectory;
        $this->module = $module;
    }

    /**
     * @todo Use more Dependency Injection with Service Container in v2.0.0
     *
     * @return LoggerInterface
     */
    public function build()
    {
        $rotatingFileHandler = new RotatingFileHandler(
            $this->loggerDirectory->getPath() . $this->module->name . '-' . \Context::getContext()->shop->id,
            (int) $this->getFromConfiguration('PS_CHECKOUT_LOGGER_MAX_FILES', 15),
            (int) $this->getFromConfiguration('PS_CHECKOUT_LOGGER_LEVEL', Logger::ERROR)
        );
        $lineFormatter = new LineFormatter(
            LineFormatter::SIMPLE_FORMAT,
            LineFormatter::SIMPLE_DATE,
            false,
            false
        );
        $rotatingFileHandler->setFormatter($lineFormatter);

        return new Logger(
            $this->module->name,
            [
                $rotatingFileHandler,
            ],
            [
                new PsrLogMessageProcessor(),
            ]
        );
    }

    /**
     * @todo To be removed in v2.0.0
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    private function getFromConfiguration($key, $default)
    {
        if (false === \Configuration::hasKey($key)) {
            return $default;
        }

        return \Configuration::get(
            $key,
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
    }
}
