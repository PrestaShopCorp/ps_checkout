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

namespace PrestaShop\Module\PrestashopCheckout\System;

class SystemConfiguration
{
    /**
     * @return bool
     */
    public function isApacheServer()
    {
        if (php_sapi_name() === 'apache2handler') {
            return true;
        }

        if (function_exists('apache_get_version')) {
            return true;
        }

        if (isset($_SERVER['SERVER_SOFTWARE']) && stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
            return true;
        }

        if (isset($_SERVER['HTTPD_SERVER_ADMIN']) || isset($_SERVER['HTTPD_SERVER_NAME'])) {
            return true;
        }

        $headers = $this->getAllHeaders();
        if (isset($headers['Server']) && stripos($headers['Server'], 'apache') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        return [];
    }
}
