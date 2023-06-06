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

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use Exception;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

class BatchConfigurationValidator
{
    const BLACKLISTED_CONFIGURATION_KEYS = [
        PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT,
        PayPalConfiguration::PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT,
        PayPalConfiguration::PS_CHECKOUT_PAYPAL_EMAIL_STATUS,
        PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS,
    ];

    /**
     * @param array $configuration
     *
     * @throws Exception
     */
    public function validateAjaxBatchConfiguration($configuration)
    {
        if (empty($configuration) || !is_array($configuration)) {
            throw new Exception("Config can't be empty");
        }

        foreach ($configuration as $configurationItem) {
            if (empty($configurationItem['name']) || 0 !== strpos($configurationItem['name'], 'PS_CHECKOUT_')) {
                throw new Exception('Received invalid configuration key');
            }

            if (array_search($configurationItem['name'], self::BLACKLISTED_CONFIGURATION_KEYS)) {
                throw new Exception('Received blacklisted configuration key');
            }
        }
    }
}
