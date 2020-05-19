<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Cache;

/**
 * Class responsible for returning cache directory path.
 */
class CacheDirectory
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
     * @var bool PrestaShop Debug Mode
     */
    private $psIsDebugMode;

    /**
     * @param string $psVersion
     * @param string $psPath
     * @param bool $psIsDebugMode
     */
    public function __construct($psVersion, $psPath, $psIsDebugMode)
    {
        $this->psVersion = $psVersion;
        $this->psPath = $psPath;
        $this->psIsDebugMode = $psIsDebugMode;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (defined('_PS_CACHE_DIR_')) {
            return constant('_PS_CACHE_DIR_');
        }

        $path = '/var/cache/' . $this->getEnvName();

        if (version_compare($this->psVersion, '1.7.0.0', '<')) {
            $path = '/cache';
        } elseif (version_compare($this->psVersion, '1.7.4.0', '<')) {
            $path = '/app/cache/' . $this->getEnvName();
        }

        return $this->psPath . $path;
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

    /**
     * @return string
     */
    private function getEnvName()
    {
        return $this->psIsDebugMode ? 'dev' : 'prod';
    }
}
