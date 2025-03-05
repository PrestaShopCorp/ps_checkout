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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use Monolog\Logger;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayLaterConfiguration;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the configuration module
 */
class ConfigurationModule implements PresenterInterface
{
    public function __construct(
        private PayPalPayLaterConfiguration $payLaterConfiguration,
        private ExpressCheckoutConfiguration $ecConfiguration,
        private PayPalConfiguration $paypalConfiguration,
        private FundingSourceProvider $fundingSourceProvider,
        private \Ps_checkout $psCheckout,
    ) {
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
                'isFundingSourceCardEnabled' => $this->isFundingSourceCardEnabled(),
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
                'payLater' => [
                    'orderPage' => [
                        'message' => $this->payLaterConfiguration->isOrderPageMessageActive(),
                        'banner' => $this->payLaterConfiguration->isOrderPageBannerActive(),
                        'button' => $this->payLaterConfiguration->isOrderPageButtonActive(),
                    ],
                    'cartPage' => [
                        'button' => $this->payLaterConfiguration->isCartPageButtonActive(),
                    ],
                    'productPage' => [
                        'message' => $this->payLaterConfiguration->isProductPageMessageActive(),
                        'banner' => $this->payLaterConfiguration->isProductPageBannerActive(),
                        'button' => $this->payLaterConfiguration->isProductPageButtonActive(),
                    ],
                    'categoryPage' => [
                        'banner' => $this->payLaterConfiguration->isCategoryPageBannerActive(),
                    ],
                    'homePage' => [
                        'banner' => $this->payLaterConfiguration->isHomePageBannerActive(),
                    ],
                ],
                'paypalButton' => $this->paypalConfiguration->getButtonConfiguration(),
                'nonDecimalCurrencies' => $this->checkNonDecimalCurrencies(),
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
        return $this->fundingSourceProvider->getAll(true);
    }

    /**
     * Is funding source card enabled
     *
     * @return bool
     */
    private function isFundingSourceCardEnabled()
    {
        foreach ($this->fundingSourceProvider->getAll(true) as $fundingSource) {
            if ('card' === $fundingSource->name) {
                return $fundingSource->isEnabled;
            }
        }

        return false;
    }

    /**
     * Checks if any currencies are enabled for which PayPal doesn't support decimal values
     * Returns error message with listed currencies that have to be configured correctly
     *
     * @return array
     */
    private function checkNonDecimalCurrencies()
    {
        $nonDecimalCurrencies = ['HUF', 'JPY', 'TWD'];

        // Enabled currencies for PrestaShop Checkout
        $enabledCurrencies = \Currency::getPaymentCurrencies($this->psCheckout->id);

        $misConfiguredCurrencies = [];

        foreach ($enabledCurrencies as $currency) {
            if (in_array($currency['iso_code'], $nonDecimalCurrencies)) {
                $misConfiguredCurrencies[] = $currency['iso_code'];
            }
        }

        $implodedMisconfiguredCurrencies = implode(', ', $misConfiguredCurrencies);

        return [
            'showError' => !empty($misConfiguredCurrencies),
            'currencies' => $implodedMisconfiguredCurrencies,
        ];
    }
}
