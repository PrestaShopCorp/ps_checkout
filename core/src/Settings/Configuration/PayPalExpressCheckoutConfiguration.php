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

namespace PsCheckout\Core\Settings\Configuration;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class PayPalExpressCheckoutConfiguration
{
    const PS_CHECKOUT_EC_ORDER_PAGE = 'PS_CHECKOUT_EC_ORDER_PAGE';

    const PS_CHECKOUT_EC_CHECKOUT_PAGE = 'PS_CHECKOUT_EC_CHECKOUT_PAGE';

    const PS_CHECKOUT_EC_PRODUCT_PAGE = 'PS_CHECKOUT_EC_PRODUCT_PAGE';

    const PS_GUEST_CHECKOUT_ENABLED = 'PS_GUEST_CHECKOUT_ENABLED';

    /**
     * Buttons that start an Express Checkout: the Express Checkout and Pay Later buttons.
     */
    const BUTTON_KEYS = [
        self::PS_CHECKOUT_EC_ORDER_PAGE,
        self::PS_CHECKOUT_EC_CHECKOUT_PAGE,
        self::PS_CHECKOUT_EC_PRODUCT_PAGE,
        PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON,
        PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON,
        PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON,
    ];

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Single rule for both displaying a button and accepting its payment:
     * Express Checkout needs guest checkout, whatever the button configuration is.
     *
     * @param string $buttonKey One of self::BUTTON_KEYS
     *
     * @return bool
     */
    public function isButtonEnabled(string $buttonKey): bool
    {
        return in_array($buttonKey, self::BUTTON_KEYS, true)
            && $this->configuration->getBoolean(self::PS_GUEST_CHECKOUT_ENABLED)
            && $this->configuration->getBoolean($buttonKey);
    }

    /**
     * @return bool
     */
    public function isAnyButtonEnabled(): bool
    {
        foreach (self::BUTTON_KEYS as $buttonKey) {
            if ($this->isButtonEnabled($buttonKey)) {
                return true;
            }
        }

        return false;
    }
}
