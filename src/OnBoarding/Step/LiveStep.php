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

class LiveStep
{
    const VIEWED_LIVE_STEP = 'PS_CHECKOUT_LIVE_STEP_VIEWED';
    const CONFIG_LIVE_STEP = 'PS_CHECKOUT_LIVE_STEP_CONFIRMED';

    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Sets the step live flag to know if is complete and the banner closed
     *
     * @param bool $confirmed
     *
     * @return void
     */
    public function confirmed($confirmed)
    {
        $this->configuration->set(static::CONFIG_LIVE_STEP, (bool) $confirmed);
    }

    /**
     * Check if the step live is complete and the banner closed
     *
     * @return bool
     */
    public function isConfirmed()
    {
        return (bool) $this->configuration->get(static::CONFIG_LIVE_STEP);
    }

    /**
     * Sets the step live flag to know if is complete and viewed for the first time
     *
     * @param bool $viewed
     *
     * @return void
     */
    public function viewed($viewed)
    {
        $this->configuration->set(static::VIEWED_LIVE_STEP, (bool) $viewed);
    }

    /**
     * Check if the step live is complete and viewed for the first time
     *
     * @return bool
     */
    public function isViewed()
    {
        return (bool) $this->configuration->get(static::VIEWED_LIVE_STEP);
    }
}
