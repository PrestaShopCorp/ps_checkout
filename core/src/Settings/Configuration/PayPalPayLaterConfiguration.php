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

class PayPalPayLaterConfiguration
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration,)
    {
        $this->configuration = $configuration;
    }

    /** @deprecated used only as fallback */
    const PS_CHECKOUT_PAY_LATER_ORDER_PAGE = 'PS_CHECKOUT_PAY_IN_4X_ORDER_PAGE';

    /** @deprecated used only as fallback */
    const PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE = 'PS_CHECKOUT_PAY_IN_4X_PRODUCT_PAGE';

    /** @deprecated used only as fallback */
    const PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER = 'PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER';

    /** @deprecated used only as fallback */
    const PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER = 'PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER';

    const PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON = 'PS_CHECKOUT_PAY_IN_4X_ORDER_PAGE_BUTTON';

    const PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON = 'PS_CHECKOUT_PAY_IN_4X_CART_PAGE_BUTTON';

    const PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON = 'PS_CHECKOUT_PAY_IN_4X_PRODUCT_PAGE_BUTTON';

    const PS_CHECKOUT_PAY_LATER_CONFIG = 'PS_CHECKOUT_PAY_LATER_CONFIG';

    /**
     * Returns Pay Later customization, with fallback from old configuration values for banner and message statuses
     *
     * @return array[]
     */
    public function getPayLaterMessagingConfiguration(): array
    {
        $config = $this->configuration->getDeserializedRaw(self::PS_CHECKOUT_PAY_LATER_CONFIG);

        if (!$config) {
            return [
                'product' => ['status' => $this->configuration->getBoolean(self::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE) ? 'enabled': 'disabled'],
                'homepage' => ['status' => $this->configuration->getBoolean(self::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER) ? 'enabled': 'disabled'],
                'category' => ['status' => $this->configuration->getBoolean(self::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER) ? 'enabled': 'disabled'],
                'payment' => ['status' => $this->configuration->getBoolean(self::PS_CHECKOUT_PAY_LATER_ORDER_PAGE) ? 'enabled': 'disabled'],
                'cart' => ['status' => $this->configuration->getBoolean(self::PS_CHECKOUT_PAY_LATER_ORDER_PAGE) ? 'enabled': 'disabled'],
            ];
        }

        return $config;
    }
}
