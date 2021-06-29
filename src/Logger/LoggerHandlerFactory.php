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

namespace PrestaShop\Module\PrestashopCheckout\Logger;

use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;

class LoggerHandlerFactory
{
    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $maxFiles;

    /**
     * @var int
     */
    private $loggerLevel;

    /**
     * @param string $directory
     * @param string $filename
     * @param int $maxFiles
     * @param int $loggerLevel
     */
    public function __construct($directory, $filename, $maxFiles, $loggerLevel)
    {
        $this->directory = $directory;
        $this->filename = $filename;
        $this->maxFiles = $maxFiles;
        $this->loggerLevel = $loggerLevel;
    }

    /**
     * @return HandlerInterface
     */
    public function build()
    {
        return new RotatingFileHandler(
            $this->directory . $this->filename,
            $this->maxFiles,
            $this->loggerLevel
        );
    }
}
