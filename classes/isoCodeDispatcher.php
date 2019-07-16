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
        '' => 'AL',
        '' => 'DZ',
        '' => 'AD',
        '' => 'AO',
        '' => 'AI',
        '' => 'AG',
        '' => 'AR',
        '' => 'AM',
        '' => 'AW',
        '' => 'AU',
        '' => 'AT',
        '' => 'AZ',
        '' => 'BS',
        '' => 'BH',
        '' => 'BB',
        '' => 'BY',
        '' => 'BE',
        '' => 'BZ',
        '' => 'BJ',
        '' => 'BM',
        '' => 'BT',
        '' => 'BO',
        '' => 'BA',
        '' => 'BW',
        '' => 'BR',
        '' => 'VG',
        '' => 'BN',
        '' => 'BG',
        '' => 'BF',
        '' => 'BI',
        '' => 'KH',
        '' => 'CM',
        '' => 'CA',
        '' => 'CV',
        '' => 'KY',
        '' => 'TD',
        '' => 'CL',
        '' => 'C2',
        '' => 'CO',
        '' => 'KM',
        '' => 'CG',
        '' => 'CD',
        '' => 'CK',
        '' => 'CR',
        '' => 'CI',
        '' => 'HR',
        '' => 'CY',
        '' => 'CZ',
        '' => 'DK',
        '' => 'DJ',
        '' => 'DM',
        '' => 'DO',
        '' => 'EC',
        '' => 'EG',
        '' => 'SV',
        '' => 'ER',
        '' => 'EE',
        '' => 'ET',
        '' => 'FK',
        '' => 'FO',
        '' => 'FJ',
        '' => 'FI',
        '' => 'FR',
        '' => 'GF',
        '' => 'PF',
        '' => 'GA',
        '' => 'GM',
        '' => 'GE',
        '' => 'DE',
        '' => 'GI',
        '' => 'GR',
        '' => 'GL',
        '' => 'GD',
        '' => 'GP',
        '' => 'GT',
        '' => 'GN',
        '' => 'GW',
        '' => 'GY',
        '' => 'HN',
        '' => 'HK',
        '' => 'HU',
        '' => 'IS',
        '' => 'IN',
        '' => 'ID',
        '' => 'IE',
        '' => 'IL',
        '' => 'IT',
        '' => 'JM',
        '' => 'JP',
        '' => 'JO',
        '' => 'KZ',
        '' => 'KE',
        '' => 'KI',
        '' => 'KW',
        '' => 'KG',
        '' => 'LA',
        '' => 'LV',
        '' => 'LS',
        '' => 'LI',
        '' => 'LT',
        '' => 'LU',
        '' => 'MK',
        '' => 'MG',
        '' => 'MW',
        '' => 'MY',
        '' => 'MV',
        '' => 'ML',
        '' => 'MT',
        '' => 'MH',
        '' => 'MQ',
        '' => 'MR',
        '' => 'MU',
        '' => 'YT',
        '' => 'MX',
        '' => 'FM',
        '' => 'MD',
        '' => 'MC',
        '' => 'MN',
        '' => 'ME',
        '' => 'MS',
        '' => 'MA',
        '' => 'MZ',
        '' => 'NA',
        '' => 'NR',
        '' => 'NP',
        '' => 'NL',
        '' => 'NC',
        '' => 'NZ',
        '' => 'NI',
        '' => 'NE',
        '' => 'NG',
        '' => 'NU',
        '' => 'NF',
        '' => 'NO',
        '' => 'OM',
        '' => 'PW',
        '' => 'PA',
        '' => 'PG',
        '' => 'PY',
        '' => 'PE',
        '' => 'PH',
        '' => 'PN',
        '' => 'PL',
        '' => 'PT',
        '' => 'QA',
        '' => 'RE',
        '' => 'RO',
        '' => 'RU',
        '' => 'RW',
        '' => 'WS',
        '' => 'SM',
        '' => 'ST',
        '' => 'SA',
        '' => 'SN',
        '' => 'RS',
        '' => 'SC',
        '' => 'SL',
        '' => 'SG',
        '' => 'SK',
        '' => 'SI',
        '' => 'SB',
        '' => 'SO',
        '' => 'ZA',
        '' => 'KR',
        '' => 'ES',
        '' => 'LK',
        '' => 'SH',
        '' => 'KN',
        '' => 'LC',
        '' => 'PM',
        '' => 'VC',
        '' => 'SR',
        '' => 'SJ',
        '' => 'SZ',
        '' => 'SE',
        '' => 'CH',
        '' => 'TW',
        '' => 'TJ',
        '' => 'TZ',
        '' => 'TH',
        '' => 'TG',
        '' => 'TO',
        '' => 'TT',
        '' => 'TN',
        '' => 'TM',
        '' => 'TC',
        '' => 'TV',
        '' => 'UG',
        '' => 'UA',
        '' => 'AE',
        '' => 'GB',
        '' => 'US',
        '' => 'UY',
        '' => 'VU',
        '' => 'VA',
        '' => 'VE',
        '' => 'VN',
        '' => 'WF',
        '' => 'YE',
        '' => 'ZM',
        '' => 'ZW',
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
