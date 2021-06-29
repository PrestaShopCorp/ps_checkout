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

namespace PrestaShop\Module\PrestashopCheckout\OnBoarding\Step;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;

class ValueBanner
{
    const CONFIG_VALUE_BANNER = 'PS_CHECKOUT_VALUE_BANNER_CLOSED';

    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param bool $closed
     */
    public function closed($closed)
    {
        $this->configuration->set(static::CONFIG_VALUE_BANNER, (bool) $closed);
    }

    /**
     * Check if the value banner is closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return (bool) $this->configuration->get(static::CONFIG_VALUE_BANNER);
    }
}
