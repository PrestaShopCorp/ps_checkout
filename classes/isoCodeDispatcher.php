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

class IsoCodeDispatcher
{
    /**
     * PrestaShop ISO code to PayPal ISO code
     *
     * @var array
     */
    const MATCH_ISO_CODE = array(
        'AL' => 'AL',
        'DZ' => 'DZ',
        'AD' => 'AD',
        'AO' => 'AO',
        'AI' => 'AI',
        'AG' => 'AG',
        'AR' => 'AR',
        'AM' => 'AM',
        'AW' => 'AW',
        'AU' => 'AU',
        'AT' => 'AT',
        'AZ' => 'AZ',
        'BS' => 'BS',
        'BH' => 'BH',
        'BB' => 'BB',
        'BY' => 'BY',
        'BE' => 'BE',
        'BZ' => 'BZ',
        'BJ' => 'BJ',
        'BM' => 'BM',
        'BT' => 'BT',
        'BO' => 'BO',
        'BA' => 'BA',
        'BW' => 'BW',
        'BR' => 'BR',
        'VG' => 'VG',
        'BN' => 'BN',
        'BG' => 'BG',
        'BF' => 'BF',
        'BI' => 'BI',
        'KH' => 'KH',
        'CM' => 'CM',
        'CA' => 'CA',
        'CV' => 'CV',
        'KY' => 'KY',
        'TD' => 'TD',
        'CL' => 'CL',
        'CH' => 'C2',
        'CO' => 'CO',
        'KM' => 'KM',
        'CG' => 'CG',
        'CD' => 'CD',
        'CK' => 'CK',
        'CR' => 'CR',
        'CI' => 'CI',
        'HR' => 'HR',
        'CY' => 'CY',
        'CZ' => 'CZ',
        'DK' => 'DK',
        'DJ' => 'DJ',
        'DM' => 'DM',
        'DO' => 'DO',
        'EC' => 'EC',
        'EG' => 'EG',
        'SV' => 'SV',
        'ER' => 'ER',
        'EE' => 'EE',
        'ET' => 'ET',
        'FK' => 'FK',
        'FO' => 'FO',
        'FJ' => 'FJ',
        'FI' => 'FI',
        'FR' => 'FR',
        'GF' => 'GF',
        'PF' => 'PF',
        'GA' => 'GA',
        'GM' => 'GM',
        'GE' => 'GE',
        'DE' => 'DE',
        'GI' => 'GI',
        'GR' => 'GR',
        'GL' => 'GL',
        'GD' => 'GD',
        'GP' => 'GP',
        'GT' => 'GT',
        'GN' => 'GN',
        'GW' => 'GW',
        'GY' => 'GY',
        'HN' => 'HN',
        'HK' => 'HK',
        'HU' => 'HU',
        'IS' => 'IS',
        'IN' => 'IN',
        'ID' => 'ID',
        'IE' => 'IE',
        'IL' => 'IL',
        'IT' => 'IT',
        'JM' => 'JM',
        'JP' => 'JP',
        'JO' => 'JO',
        'KZ' => 'KZ',
        'KE' => 'KE',
        'KI' => 'KI',
        'KW' => 'KW',
        'KG' => 'KG',
        'LA' => 'LA',
        'LV' => 'LV',
        'LS' => 'LS',
        'LI' => 'LI',
        'LT' => 'LT',
        'LU' => 'LU',
        'MK' => 'MK',
        'MG' => 'MG',
        'MW' => 'MW',
        'MY' => 'MY',
        'MV' => 'MV',
        'ML' => 'ML',
        'MT' => 'MT',
        'MH' => 'MH',
        'MQ' => 'MQ',
        'MR' => 'MR',
        'MU' => 'MU',
        'YT' => 'YT',
        'MX' => 'MX',
        'FM' => 'FM',
        'MD' => 'MD',
        'MC' => 'MC',
        'MN' => 'MN',
        'ME' => 'ME',
        'MS' => 'MS',
        'MA' => 'MA',
        'MZ' => 'MZ',
        'NA' => 'NA',
        'NR' => 'NR',
        'NP' => 'NP',
        'NL' => 'NL',
        'NC' => 'NC',
        'NZ' => 'NZ',
        'NI' => 'NI',
        'NE' => 'NE',
        'NG' => 'NG',
        'NU' => 'NU',
        'NF' => 'NF',
        'NO' => 'NO',
        'OM' => 'OM',
        'PW' => 'PW',
        'PA' => 'PA',
        'PG' => 'PG',
        'PY' => 'PY',
        'PE' => 'PE',
        'PH' => 'PH',
        'PN' => 'PN',
        'PL' => 'PL',
        'PT' => 'PT',
        'QA' => 'QA',
        'RE' => 'RE',
        'RO' => 'RO',
        'RU' => 'RU',
        'RW' => 'RW',
        'WS' => 'WS',
        'SM' => 'SM',
        'ST' => 'ST',
        'SA' => 'SA',
        'SN' => 'SN',
        'RS' => 'RS',
        'SC' => 'SC',
        'SL' => 'SL',
        'SG' => 'SG',
        'SK' => 'SK',
        'SI' => 'SI',
        'SB' => 'SB',
        'SO' => 'SO',
        'ZA' => 'ZA',
        'KR' => 'KR',
        'ES' => 'ES',
        'LK' => 'LK',
        'SH' => 'SH',
        'KN' => 'KN',
        'LC' => 'LC',
        'PM' => 'PM',
        'VC' => 'VC',
        'SR' => 'SR',
        'SJ' => 'SJ',
        'SZ' => 'SZ',
        'SE' => 'SE',
        'CH' => 'CH',
        'TW' => 'TW',
        'TJ' => 'TJ',
        'TZ' => 'TZ',
        'TH' => 'TH',
        'TG' => 'TG',
        'TO' => 'TO',
        'TT' => 'TT',
        'TN' => 'TN',
        'TM' => 'TM',
        'TC' => 'TC',
        'TV' => 'TV',
        'UG' => 'UG',
        'UA' => 'UA',
        'AE' => 'AE',
        'GB' => 'GB',
        'US' => 'US',
        'UY' => 'UY',
        'VU' => 'VU',
        'VA' => 'VA',
        'VE' => 'VE',
        'VN' => 'VN',
        'WF' => 'WF',
        'YE' => 'YE',
        'ZM' => 'ZM',
        'ZW' => 'ZW',
    );

    /**
     * Get the PayPal ISO code from PrestaShop ISO Code
     *
     * @param string $isoCode
     *
     * @return string|bool
     */
    public function getPaypalIsoCode($isoCode)
    {
        if (false === array_key_exists($isoCode, self::MATCH_ISO_CODE)) {
            return false;
        }

        return self::MATCH_ISO_CODE[$isoCode];
    }

    /**
     * Get the PrestaShop ISO code from PayPal ISO Code
     *
     * @param string $isoCode
     *
     * @return string|bool
     */
    public function getPrestashopIsoCode($isoCode)
    {
        return array_search($isoCode, self::MATCH_ISO_CODE);
    }
}
