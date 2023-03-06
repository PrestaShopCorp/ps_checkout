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

use PrestaShop\Module\PrestashopCheckout\PayPal\Mode;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

/**
 * Get the current environment used: prod or test // sandbox or live
 */
class Env
{
    /**
     * Const that define all environment possible to use.
     * Top of the list are taken in first if they exist in the project.
     * eg: If .env.test is present in the module it will be loaded, if not present
     * we try to load the next one etc ...
     *
     * @var array
     */
    const FILE_ENV_LIST = [
        'test' => '.env.test',
        'prod' => '.env',
    ];

    /**
     * Environment name: can be 'prod' or 'test'
     *
     * @var string
     */
    protected $name;

    /**
     * Environment mode: can be 'live' or 'sandbox'
     *
     * @var string
     */
    protected $mode;

    public function __construct(PayPalConfiguration $payPalConfiguration)
    {
        foreach (self::FILE_ENV_LIST as $env => $fileName) {
            if (!file_exists(_PS_MODULE_DIR_ . 'ps_checkout/' . $fileName)) {
                continue;
            }

            $envLoader = new EnvLoader();
            $envLoader->load(_PS_MODULE_DIR_ . 'ps_checkout/' . $fileName, false);

            $this->setName($env);

            break;
        }

        $this->setMode($payPalConfiguration->getPaymentMode());
    }

    /**
     * getter for name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * getter for mode
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * setter for name
     *
     * @param string $name
     */
    private function setName($name)
    {
        $this->name = $name;
    }

    /**
     * setter for mode
     *
     * @param string $mode
     */
    private function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getEnv($name)
    {
        if (isset($_ENV[$name])) {
            return $_ENV[$name];
        }

        if (isset($_SERVER[$name])) {
            return $_SERVER[$name];
        }

        return getenv($name);
    }

    /**
     * getter for paymentApiUrl
     */
    public function getPaymentApiUrl()
    {
        if (Mode::SANDBOX === $this->mode) {
            return $this->getEnv('PAYMENT_API_URL_SANDBOX');
        }

        return $this->getEnv('PAYMENT_API_URL_LIVE');
    }

    /**
     * @return string
     */
    public function getCheckoutApiUrl()
    {
        if (Mode::SANDBOX === $this->mode) {
            return $this->getEnv('CHECKOUT_API_URL_SANDBOX');
        }

        return $this->getEnv('CHECKOUT_API_URL_LIVE');
    }

    /**
     * @return string
     */
    public function getPaypalClientId()
    {
        if (Mode::SANDBOX === $this->mode) {
            return $this->getEnv('PAYPAL_CLIENT_ID_SANDBOX');
        }

        return $this->getEnv('PAYPAL_CLIENT_ID_LIVE');
    }

    public function getBnCode()
    {
        return $this->getEnv('PAYPAL_BN_CODE');
    }
}
