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

namespace PsCheckout\Infrastructure\Validator;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class FrontControllerValidator implements FrontControllerValidatorInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(
        ConfigurationInterface $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldLoadFrontCss(string $controller): bool
    {
        switch ($controller) {
            // Homepage
            case 'index':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER);
                // Category
            case 'category':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER);
                // Payment step
            case 'orderopc':
            case 'order':
                // Payment methods logos (always if merchant is valid), Payment4X banner, ExpressCheckout button
            case 'product':
            case 'cart':
                return true;
                // ExpressCheckout button
            case 'authentication':
                return $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldLoadFrontJS(string $controller): bool
    {
        switch ($controller) {
            // Homepage
            case 'index':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER);
                // Category
            case 'category':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER);
            case 'orderopc':
            case 'order':
                return true;
            case 'product':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BANNER)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON)
                    || $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE)
                    || $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_PRODUCT);
            case 'cart':
                return $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON)
                    || $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE)
                    || $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_CART);
            case 'authentication':
                return $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE)
                    || $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldDisplayFundingLogo(string $controller): bool
    {
        switch ($controller) {
            case 'product':
                return $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_PRODUCT);
            case 'cart':
                return $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_CART);
        }

        return false;
    }
}
