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
use Monolog\Handler\RotatingFileHandler;
use PsCheckout\Core\Settings\Configuration\LoggerConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class LoggerHandlerFactory implements LoggerHandlerInterface
{
    private $psPath = _PS_ROOT_DIR_;

    private $psVersion = _PS_VERSION_;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var string
     */
    private $moduleName;

    public function __construct(ConfigurationInterface $configuration, ContextInterface $context, string $moduleName)
    {
        $this->configuration = $configuration;
        $this->shopId = $context->getShop()->id;
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function build(): HandlerInterface
    {
        return new RotatingFileHandler(
            $this->getPath() . $this->getFileName(),
            $this->getMaxFiles(),
            $this->getLoggerLevel()
        );
    }

    /**
     * {@inheritdoc}
     */
    private function getPath(): string
    {
        if (version_compare($this->psVersion, '1.7.4', '<')) {
            return $this->psPath . '/app/logs/';
        }

        return $this->psPath . '/var/logs/';
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return $this->moduleName . '-' . $this->shopId;
    }

    /**
     * @return int
     */
    private function getMaxFiles(): int
    {
        $maxFiles = (int) $this->configuration->get(LoggerConfiguration::PS_CHECKOUT_LOGGER_MAX_FILES);

        if (!$maxFiles) {
            return LoggerConfiguration::PS_CHECKOUT_LOGGER_MAX_FILES_DEFAULT;
        }

        return $maxFiles;
    }

    /**
     * @return int
     */
    private function getLoggerLevel(): int
    {
        $loggerLevel = (int) $this->configuration->get(LoggerConfiguration::PS_CHECKOUT_LOGGER_LEVEL);

        if (!$loggerLevel) {
            return LoggerConfiguration::LEVEL_ERROR;
        }

        return $loggerLevel;
    }
}
