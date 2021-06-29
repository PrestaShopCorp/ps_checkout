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

/**
 * Class responsible for returning log directory path.
 */
class LoggerDirectory
{
    /**
     * @var string PrestaShop version
     */
    private $psVersion;

    /**
     * @var string PrestaShop path
     */
    private $psPath;

    /**
     * @param string $psVersion
     * @param string $psPath
     */
    public function __construct($psVersion, $psPath)
    {
        $this->psVersion = $psVersion;
        $this->psPath = $psPath;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (version_compare($this->psVersion, '1.7', '<')) {
            return $this->psPath . '/log/';
        } elseif (version_compare($this->psVersion, '1.7.4', '<')) {
            return $this->psPath . '/app/logs/';
        }

        return $this->psPath . '/var/logs/';
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return is_writable($this->getPath());
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return is_readable($this->getPath());
    }
}
