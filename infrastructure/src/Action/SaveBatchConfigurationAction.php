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

namespace PsCheckout\Infrastructure\Action;

use Exception;
use InvalidArgumentException;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use RuntimeException;

class SaveBatchConfigurationAction implements SaveBatchConfigurationActionInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(array $configuration)
    {
        $this->validateBatchConfiguration($configuration);

        try {
            foreach ($configuration as $configurationItem) {
                $this->configuration->set($configurationItem['name'], $configurationItem['value']);
            }
        } catch (Exception $exception) {
            throw new RuntimeException('Could not save configuration: ' . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param array $configuration an associative array where keys are configuration names and values are the corresponding settings to be saved
     *
     * @return void
     */
    private function validateBatchConfiguration(array $configuration)
    {
        $blacklistedConfigurationKeys = [
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT,
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_EMAIL_STATUS,
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS,
        ];

        if (empty($configuration)) {
            throw new InvalidArgumentException("Config can't be empty");
        }

        foreach ($configuration as $configurationItem) {
            if (!is_array($configurationItem)
                || !array_key_exists('name', $configurationItem)
                || !array_key_exists('value', $configurationItem)
                || empty($configurationItem['name'])
                || 0 !== strpos($configurationItem['name'], 'PS_CHECKOUT_')
            ) {
                throw new InvalidArgumentException('Received invalid configuration key');
            }

            if (in_array($configurationItem['name'], $blacklistedConfigurationKeys, true)) {
                throw new InvalidArgumentException('Received blacklisted configuration key');
            }
        }
    }
}
