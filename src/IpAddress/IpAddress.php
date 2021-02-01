<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\IpAddress;

use \Iriven\GeoIPCountry;

class IpAddress
{
    /**
     * Get client IP adresss
     *
     * @return null|string
     */
    public function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) { // IP from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { // IP pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Get client country code from his IP address
     *
     * @return null|string
     */
    public function getClientCountryCode() {
        require_once __DIR__ . '/GeoIPCountry/GeoIPCountry.php';

        $Ip2Country = new \Iriven\GeoIPCountry();
        $countryCode = $Ip2Country->resolve($this->getClientIp);

        if (!$countryCode) {
            $countryCode = json_decode(file_get_contents('http://www.geoplugin.net/json.gp?ip=' . $this->getClient()))->geoplugin_countryCode;
        }

        return $countryCode;
    }
}
