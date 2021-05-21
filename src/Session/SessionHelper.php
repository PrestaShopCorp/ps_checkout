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

class SessionHelper
{
    /**
     * Defines is session is expired
     *
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public static function isExpired(Session $session)
    {
        if (
            $session->getExpiresAt() &&
            date_create(date('Y-m-d H:i:s')) > date_create($session->getExpiresAt())
        ) {
            return true;
        }

        return false;
    }

    /**
     * Update a session expiration date
     *
     * @param string $refDate
     * @param string $intervalDate 2 hours by default
     *
     * @return string
     */
    public static function updateExpirationDate($refDate, $intervalDate = '2 hours')
    {
        return date_format(date_add(date_create($refDate), date_interval_create_from_date_string($intervalDate)), 'Y-m-d H:i:s');
    }
}
