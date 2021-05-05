<?php

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

        if(!file_exists($path)) {
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
