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
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the configuration module
 */
class ConfigurationModule implements PresenterInterface
{
    /**
     * @var ExpressCheckoutConfiguration
     */
    private $ecConfiguration;

    /**
     * @var PayPalConfiguration
     */
    private $paypalConfiguration;

    /**
     * @param ExpressCheckoutConfiguration $ecConfiguration
     * @param PayPalConfiguration $paypalConfiguration
     */
    public function __construct(ExpressCheckoutConfiguration $ecConfiguration, PayPalConfiguration $paypalConfiguration)
    {
        $this->ecConfiguration = $ecConfiguration;
        $this->paypalConfiguration = $paypalConfiguration;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        return [
            'config' => [
                'paymentMethods' => $this->getPaymentMethods(),
                'captureMode' => $this->paypalConfiguration->getIntent(),
                'paymentMode' => $this->paypalConfiguration->getPaymentMode(),
                'cardIsEnabled' => $this->paypalConfiguration->isCardPaymentEnabled(),
                'cardInlinePaypalIsEnabled' => $this->paypalConfiguration->isCardInlinePaypalIsEnabled(),
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
                    'level' => (int) \Configuration::getGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_LEVEL),
                    'maxFiles' => (int) \Configuration::getGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_MAX_FILES),
                    'http' => (int) \Configuration::getGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP),
                    'httpFormat' => \Configuration::getGlobalValue(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP_FORMAT),
                ],
                'expressCheckout' => [
                    'orderPage' => (bool) $this->ecConfiguration->isOrderPageEnabled(),
                    'checkoutPage' => (bool) $this->ecConfiguration->isCheckoutPageEnabled(),
                    'productPage' => (bool) $this->ecConfiguration->isProductPageEnabled(),
                ],
            ],
        ];
    }

    /**
     * Get payment methods order
     *
     * @return array payment method
     */
    private function getPaymentMethods()
    {
        $paymentMethods = $this->paypalConfiguration->getPaymentMethodsOrder();

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
