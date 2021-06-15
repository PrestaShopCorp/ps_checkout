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

namespace PrestaShop\Module\PrestashopCheckout\Session;

use Symfony\Component\Yaml\Yaml;

class SessionConfiguration
{
    /**
     * Get the whole session configuration
     *
     * @return array
     */
    public function get()
    {
        return Yaml::parseFile(_PS_MODULE_DIR_ . '/ps_checkout/config/session.yml');
    }

    /**
     * Get the onboarding session configuration
     *
     * @return array
     */
    public function getOnboarding()
    {
        return $this->get()['onboarding'];
    }
}
