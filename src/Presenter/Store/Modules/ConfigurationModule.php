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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use Monolog\Logger;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the configuration module
 */
class ConfigurationModule implements PresenterInterface
{
    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var PrestaShopConfiguration $configuration */
        $configuration = $module->getService('ps_checkout.configuration');

        $configurationModule = [
            'config' => [
                'paymentMethods' => $this->getPaymentMethods(),
                'captureMode' => $module->getService('ps_checkout.paypal.configuration')->getIntent(),
                'paymentMode' => $configuration->get('PS_CHECKOUT_MODE'),
                'cardIsEnabled' => (bool) $configuration->get('PS_CHECKOUT_CARD_PAYMENT_ENABLED'),
                'logger' => [
                    'levels' => [
                        Logger::DEBUG => 'DEBUG : Detailed debug information',
                        // Logger::INFO => 'INFO : Interesting events',
                        // Logger::NOTICE => 'NOTICE : Normal but significant events',
                        // Logger::WARNING => 'WARNING : Exceptional occurrences that are not errors',
                        Logger::ERROR => 'ERROR : Runtime errors that do not require immediate action',
                        // Logger::CRITICAL => 'CRITICAL : Critical conditions',
                        // Logger::ALERT => 'ALERT : Action must be taken immediately',
                        // Logger::EMERGENCY => 'EMERGENCY : system is unusable',
                    ],
                    'httpFormats' => [
                        'CLF' => 'Apache Common Log Format',
                        'DEBUG' => 'Debug format',
                        'SHORT' => 'Short format',
                    ],
                    'level' => (int) \Configuration::getGlobalValue('PS_CHECKOUT_LOGGER_LEVEL'),
                    'maxFiles' => (int) \Configuration::getGlobalValue('PS_CHECKOUT_LOGGER_MAX_FILES'),
                    'http' => (int) \Configuration::getGlobalValue('PS_CHECKOUT_LOGGER_HTTP'),
                    'httpFormat' => \Configuration::getGlobalValue('PS_CHECKOUT_LOGGER_HTTP_FORMAT'),
                ],
                'expressCheckout' => [
                    'orderPage' => (bool) $configuration->get('PS_CHECKOUT_EC_ORDER_PAGE'),
                    'checkoutPage' => (bool) $configuration->get('PS_CHECKOUT_EC_CHECKOUT_PAGE'),
                    'productPage' => (bool) $configuration->get('PS_CHECKOUT_EC_PRODUCT_PAGE'),
                ],
            ],
        ];

        return $configurationModule;
    }

    /**
     * Get payment methods order
     *
     * @return array payment method
     */
    private function getPaymentMethods()
    {
        $paymentMethods = \Configuration::get(
            'PS_CHECKOUT_PAYMENT_METHODS_ORDER',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        if (empty($paymentMethods)) {
            $paymentMethods = [
                ['name' => 'card'],
                ['name' => 'paypal'],
            ];
        } else {
            $paymentMethods = json_decode($paymentMethods, true);
        }

        return $paymentMethods;
    }
}
