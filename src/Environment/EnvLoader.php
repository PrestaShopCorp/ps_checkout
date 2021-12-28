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

namespace PrestaShop\Module\PrestashopCheckout\Environment;

class EnvLoader
{
    /**
     * @var bool
     */
    private $apacheEnvSupported;
    /**
     * @var bool
     */
    private $putEnvSupported;
    /**
     * @var bool
     */
    private $quiet;

    public function __construct($quiet = true)
    {
        $this->apacheEnvSupported = $this->apacheSetEnvIsSupported();
        $this->putEnvSupported = $this->putEnvIsSupported();
        $this->quiet = $quiet;
    }

    /**
     * @param string $path
     * @param bool $overwriteExistingVariables
     */
    public function load($path, $overwriteExistingVariables = true)
    {
        $envVariables = $this->read($path);

        foreach ($envVariables as $name => $value) {
            if ($overwriteExistingVariables || (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV))) {
                $this->setVariable($name, $value);
            }
        }

        return $envVariables;
    }

    /**
     * Returns ENV values as array, but doesn't load them as global variables
     *
     * @param string $path
     *
     * @return array
     */
    public function read($path)
    {
        if (!$this->checkFile($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $envVariables = [];

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim(trim($name), '"');
            $value = trim(trim($value), '"');

            $envVariables[$name] = $value;
        }

        return $envVariables;
    }

    /**
     * @return bool
     */
    public function apacheSetEnvIsSupported()
    {
        return function_exists('apache_getenv') && function_exists('apache_setenv');
    }

    /**
     * @return bool
     */
    public function putEnvIsSupported()
    {
        return function_exists('getenv') && function_exists('putenv');
    }

    /**
     * @param string $path
     */
    private function checkFile($path)
    {
        if ($this->quiet && (!file_exists($path) || !is_readable($path))) {
            return false;
        }

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('%s does not exist', $path));
        }

        if (!is_readable($path)) {
            throw new \RuntimeException(sprintf('%s file is not readable', $path));
        }

        return true;
    }

    /**
     * @param string|int $name
     * @param string|int|bool $value
     */
    private function setVariable($name, $value)
    {
        if ($this->putEnvSupported) {
            putenv(sprintf('%s=%s', $name, $value));
        }
        if ($this->apacheEnvSupported) {
            apache_setenv($name, $value);
        }

        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}
