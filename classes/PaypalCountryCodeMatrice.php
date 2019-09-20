<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

class PaypalCountryCodeMatrice
{
    /**
     * PrestaShop ISO code to PayPal ISO code
     *
     * @var array
     */
    const MATCH_ISO_CODE = array(
        'CN' => 'C2',
    );

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
