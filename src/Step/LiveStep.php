<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Step;

class LiveStep
{
    const CONFIG_LIVE_STEP = 'PS_CHECKOUT_LIVE_STEP_CONFIRMED';

    /**
     * @param bool $confirmed
     *
     * @return mixed
     */
    public function confirmed($confirmed)
    {
        return \Configuration::updateValue(
            static::CONFIG_LIVE_STEP,
            $confirmed,
            false,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

    /**
     * @return false|mixed|string
     */
    public function isConfirmed()
    {
        return \Configuration::get(
                static::CONFIG_LIVE_STEP,
            null,
            null,
            (int) \Context::getContext()->shop->id) === '1';
    }
}
