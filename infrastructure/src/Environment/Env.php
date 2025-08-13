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

namespace PsCheckout\Infrastructure\Environment;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

/**
 * Get the current environment used: prod or test // sandbox or live
 */
class Env implements EnvInterface
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
     * Environment mode: can be 'live' or 'sandbox'
     *
     * @var string
     */
    protected $mode;

    /**
     * @param string $moduleName
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        string $moduleName,
        ConfigurationInterface $configuration
    ) {
        foreach (self::FILE_ENV_LIST as $env => $fileName) {
            $envFilePath = _PS_MODULE_DIR_ . $moduleName . '/' . $fileName;

            if (!file_exists($envFilePath)) {
                continue;
            }

            $envLoader = new EnvLoader();
            $envLoader->load($envFilePath, false);

            break;
        }

        $this->mode = $configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getEnv(string $name)
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
     * {@inheritdoc}
     */
    public function getCheckoutApiUrl(): string
    {
        if (PayPalConfiguration::MODE_SANDBOX === $this->mode) {
            return $this->getEnv('CHECKOUT_API_URL_SANDBOX');
        }

        return $this->getEnv('CHECKOUT_API_URL_LIVE');
    }

    /**
     * {@inheritdoc}
     */
    public function getShipmentTrackingApiUrl(): string
    {
        if (PayPalConfiguration::MODE_SANDBOX === $this->mode) {
            return $this->getEnv('TRACKING_API_URL_SANDBOX');
        }

        return $this->getEnv('TRACKING_API_URL_LIVE');
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentApiUrl(): string
    {
        if (PayPalConfiguration::MODE_SANDBOX === $this->mode) {
            return $this->getEnv('PAYMENT_API_URL_SANDBOX');
        }

        return $this->getEnv('PAYMENT_API_URL_LIVE');
    }

    /**
     * {@inheritdoc}
     */
    public function getPaypalClientId(): string
    {
        if (PayPalConfiguration::MODE_SANDBOX === $this->mode) {
            return $this->getEnv('PAYPAL_CLIENT_ID_SANDBOX');
        }

        return $this->getEnv('PAYPAL_CLIENT_ID_LIVE');
    }

    /**
     * {@inheritdoc}
     */
    public function getBnCode(): string
    {
        return $this->getEnv('PAYPAL_BN_CODE');
    }
}
