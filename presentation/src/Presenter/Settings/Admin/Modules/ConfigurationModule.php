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

namespace PsCheckout\Presentation\Presenter\Settings\Admin\Modules;

use Monolog\Logger;
use PsCheckout\Core\Settings\Configuration\LoggerConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

/**
 * Construct the configuration module
 */
class ConfigurationModule implements PresenterInterface
{
    /**
     * @var int
     */
    private $moduleId;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var FundingSourcePresenterInterface
     */
    private $fundingSourcePresenter;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @param int $moduleId
     * @param ConfigurationInterface $configuration
     * @param FundingSourcePresenterInterface $fundingSourcePresenter
     * @param int $shopId
     */
    public function __construct(
        int $moduleId,
        ConfigurationInterface $configuration,
        FundingSourcePresenterInterface $fundingSourcePresenter,
        int $shopId
    ) {
        $this->moduleId = $moduleId;
        $this->configuration = $configuration;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->shopId = $shopId;
    }

    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present(): array
    {
        return [
            'config' => [
                'paymentMethods' => $this->fundingSourcePresenter->getAllForSpecificShop($this->shopId),
                'nonDecimalCurrencies' => $this->checkNonDecimalCurrencies(),
                'cardIsEnabled' => $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_CARD_PAYMENT_ENABLED),
                'paypalButton' => $this->configuration->getDeserializedRaw(PayPalConfiguration::PS_CHECKOUT_PAYPAL_BUTTON),
                'expressCheckout' => [
                    'orderPage' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE),
                    'checkoutPage' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE),
                    'productPage' => $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE),
                ],
                'payLater' => [
                    'orderPage' => [
                        'message' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE),
                        'banner' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER),
                        'button' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON),
                    ],
                    'cartPage' => [
                        'button' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON),
                    ],
                    'productPage' => [
                        'message' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE),
                        'banner' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BANNER),
                        'button' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON),
                    ],
                    'categoryPage' => [
                        'banner' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER),
                    ],
                    'homePage' => [
                        'banner' => $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER),
                    ],
                ],
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
                    'level' => $this->configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_LEVEL),
                    'maxFiles' => $this->configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_MAX_FILES),
                    'http' => $this->configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP),
                    'httpFormat' => $this->configuration->get(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP_FORMAT),
                ],
            ],
        ];
    }

    /**
     * Checks if any currencies are enabled for which PayPal doesn't support decimal values
     * Returns error message with listed currencies that have to be configured correctly
     *
     * @return array
     */
    private function checkNonDecimalCurrencies(): array
    {
        $nonDecimalCurrencies = ['HUF', 'JPY', 'TWD'];

        // Enabled currencies for PrestaShop Checkout
        $enabledCurrencies = \Currency::getPaymentCurrencies($this->moduleId);

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
