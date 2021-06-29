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

namespace PrestaShop\Module\PrestashopCheckout;

class PaypalCountryCodeMatrice
{
    /**
     * PrestaShop ISO code to PayPal ISO code
     *
     * @var array
     */
    const MATCH_ISO_CODE = [
        'CN' => 'C2',
    ];

    /**
     * Get the PayPal ISO code from PrestaShop ISO Code
     *
     * @param string $isoCode
     *
     * @return string|false
     */
    public function getPaypalIsoCode($isoCode)
    {
        if (!is_string($isoCode)) {
            return false;
        }

        if (false === array_key_exists($isoCode, self::MATCH_ISO_CODE)) {
            return $isoCode;
        }

        return self::MATCH_ISO_CODE[$isoCode];
    }

    /**
     * Get the PrestaShop ISO code from PayPal ISO Code
     *
     * @param string $isoCode
     *
     * @return string|false
     */
    public function getPrestashopIsoCode($isoCode)
    {
        if (!is_string($isoCode)) {
            return false;
        }

        if (false === array_search($isoCode, self::MATCH_ISO_CODE)) {
            return $isoCode;
        }

        return array_search($isoCode, self::MATCH_ISO_CODE);
    }
}
