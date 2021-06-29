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


namespace PrestaShop\Module\PrestashopCheckout\Handler;

use Raven_Client;

/**
 * Inheritance allow us to check data generated by Raven and filter errors
 * that are not related to the module.
 * Raven does not filter errors by itself depending on the appPath and any
 * excludedAppPaths, but declares what phase of the stack trace is outside the app.
 * We use this data to allow each module filtering their own errors.
 *
 * IMPORTANT NOTE: This class is present is this module during the
 * stabilisation phase, and will be moved later in a library.
 */
class ModuleFilteredRavenClient extends Raven_Client
{
    /**
     * @var string[]|null
     */
    protected $excluded_domains;

    /**
     * @param string $dsn
     * @param array $options
     */
    public function __construct($dsn, array $options = [])
    {
        parent::__construct($dsn, $options);
    }

    /**
     * @param mixed $data
     * @param mixed $stack
     * @param mixed $vars
     *
     * @return mixed
     */
    public function capture($data, $stack = null, $vars = null)
    {
        if (!isset($data['exception']['values'][0]['stacktrace']['frames'])) {
            return null;
        }

        if ($this->isErrorFilteredByContext()) {
            return null;
        }

        $allowCapture = false;
        foreach ($data['exception']['values'] as $errorValues) {
            $allowCapture = $allowCapture || $this->isErrorInApp($errorValues);
        }

        if (!$allowCapture) {
            return null;
        }

        return parent::capture($data, $stack, $vars);
    }

    /**
     * @return self
     */
    public function setExcludedDomains(array $domains)
    {
        $this->excluded_domains = $domains;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function isErrorInApp(array $data)
    {
        $atLeastOneFileIsInApp = false;
        foreach ($data['stacktrace']['frames'] as $frame) {
            $atLeastOneFileIsInApp = $atLeastOneFileIsInApp || ((isset($frame['in_app']) && $frame['in_app']));
        }

        return $atLeastOneFileIsInApp;
    }

    /**
     * Check the conditions in which the error is thrown, so we can apply filters
     *
     * @return bool
     */
    private function isErrorFilteredByContext()
    {
        if ($this->excluded_domains && !empty($_SERVER['REMOTE_ADDR'])) {
            foreach ($this->excluded_domains as $domain) {
                if (false !== strpos($_SERVER['REMOTE_ADDR'], $domain)) {
                    return true;
                }
            }
        }

        return false;
    }
}
