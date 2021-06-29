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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

class PayPalCodeRepository
{
    /**
     * Get the ISO country codes supported by PayPal (IS0-3166-1)
     *
     * @return array
     */
    public function getCountryCodes()
    {
        return [
            'AL' => 'ALBANIA',
            'DZ' => 'ALGERIA',
            'AD' => 'ANDORRA',
            'AO' => 'ANGOLA',
            'AI' => 'ANGUILLA',
            'AG' => 'ANTIGUA & BARBUDA',
            'AR' => 'ARGENTINA',
            'AM' => 'ARMENIA',
            'AW' => 'ARUBA',
            'AU' => 'AUSTRALIA',
            'AT' => 'AUSTRIA',
            'AZ' => 'AZERBAIJAN',
            'BS' => 'BAHAMAS',
            'BH' => 'BAHRAIN',
            'BB' => 'BARBADOS',
            'BY' => 'BELARUS',
            'BE' => 'BELGIUM',
            'BZ' => 'BELIZE',
            'BJ' => 'BENIN',
            'BM' => 'BERMUDA',
            'BT' => 'BHUTAN',
            'BO' => 'BOLIVIA',
            'BA' => 'BOSNIA & HERZEGOVINA',
            'BW' => 'BOTSWANA',
            'BR' => 'BRAZIL',
            'VG' => 'BRITISH VIRGIN ISLANDS',
            'BN' => 'BRUNEI',
            'BG' => 'BULGARIA',
            'BF' => 'BURKINA FASO',
            'BI' => 'BURUNDI',
            'KH' => 'CAMBODIA',
            'CM' => 'CAMEROON',
            'CA' => 'CANADA',
            'CV' => 'CAPE VERDE',
            'KY' => 'CAYMAN ISLANDS',
            'TD' => 'CHAD',
            'CL' => 'CHILE',
            'CN' => 'CHINA',
            'CO' => 'COLOMBIA',
            'KM' => 'COMOROS',
            'CG' => 'CONGO - BRAZZAVILLE',
            'CD' => 'CONGO - KINSHASA',
            'CK' => 'COOK ISLANDS',
            'CR' => 'COSTA RICA',
            'CI' => 'CÔTE D’IVOIRE',
            'HR' => 'CROATIA',
            'CY' => 'CYPRUS',
            'CZ' => 'CZECH REPUBLIC',
            'DK' => 'DENMARK',
            'DJ' => 'DJIBOUTI',
            'DM' => 'DOMINICA',
            'DO' => 'DOMINICAN REPUBLIC',
            'EC' => 'ECUADOR',
            'EG' => 'EGYPT',
            'SV' => 'EL SALVADOR',
            'ER' => 'ERITREA',
            'EE' => 'ESTONIA',
            'ET' => 'ETHIOPIA',
            'FK' => 'FALKLAND ISLANDS',
            'FO' => 'FAROE ISLANDS',
            'FJ' => 'FIJI',
            'FI' => 'FINLAND',
            'FR' => 'FRANCE',
            'GF' => 'FRENCH GUIANA',
            'PF' => 'FRENCH POLYNESIA',
            'GA' => 'GABON',
            'GM' => 'GAMBIA',
            'GE' => 'GEORGIA',
            'DE' => 'GERMANY',
            'GI' => 'GIBRALTAR',
            'GR' => 'GREECE',
            'GL' => 'GREENLAND',
            'GD' => 'GRENADA',
            'GP' => 'GUADELOUPE',
            'GT' => 'GUATEMALA',
            'GN' => 'GUINEA',
            'GW' => 'GUINEA-BISSAU',
            'GY' => 'GUYANA',
            'HN' => 'HONDURAS',
            'HK' => 'HONG KONG SAR CHINA',
            'HU' => 'HUNGARY',
            'IS' => 'ICELAND',
            'IN' => 'INDIA',
            'ID' => 'INDONESIA',
            'IE' => 'IRELAND',
            'IL' => 'ISRAEL',
            'IT' => 'ITALY',
            'JM' => 'JAMAICA',
            'JP' => 'JAPAN',
            'JO' => 'JORDAN',
            'KZ' => 'KAZAKHSTAN',
            'KE' => 'KENYA',
            'KI' => 'KIRIBATI',
            'KW' => 'KUWAIT',
            'KG' => 'KYRGYZSTAN',
            'LA' => 'LAOS',
            'LV' => 'LATVIA',
            'LS' => 'LESOTHO',
            'LI' => 'LIECHTENSTEIN',
            'LT' => 'LITHUANIA',
            'LU' => 'LUXEMBOURG',
            'MK' => 'MACEDONIA',
            'MG' => 'MADAGASCAR',
            'MW' => 'MALAWI',
            'MY' => 'MALAYSIA',
            'MV' => 'MALDIVES',
            'ML' => 'MALI',
            'MT' => 'MALTA',
            'MH' => 'MARSHALL ISLANDS',
            'MQ' => 'MARTINIQUE',
            'MR' => 'MAURITANIA',
            'MU' => 'MAURITIUS',
            'YT' => 'MAYOTTE',
            'MX' => 'MEXICO',
            'FM' => 'MICRONESIA',
            'MD' => 'MOLDOVA',
            'MC' => 'MONACO',
            'MN' => 'MONGOLIA',
            'ME' => 'MONTENEGRO',
            'MS' => 'MONTSERRAT',
            'MA' => 'MOROCCO',
            'MZ' => 'MOZAMBIQUE',
            'NA' => 'NAMIBIA',
            'NR' => 'NAURU',
            'NP' => 'NEPAL',
            'NL' => 'NETHERLANDS',
            'NC' => 'NEW CALEDONIA',
            'NZ' => 'NEW ZEALAND',
            'NI' => 'NICARAGUA',
            'NE' => 'NIGER',
            'NG' => 'NIGERIA',
            'NU' => 'NIUE',
            'NF' => 'NORFOLK ISLAND',
            'NO' => 'NORWAY',
            'OM' => 'OMAN',
            'PW' => 'PALAU',
            'PA' => 'PANAMA',
            'PG' => 'PAPUA NEW GUINEA',
            'PY' => 'PARAGUAY',
            'PE' => 'PERU',
            'PH' => 'PHILIPPINES',
            'PN' => 'PITCAIRN ISLANDS',
            'PL' => 'POLAND',
            'PT' => 'PORTUGAL',
            'QA' => 'QATAR',
            'RE' => 'RÉUNION',
            'RO' => 'ROMANIA',
            'RU' => 'RUSSIA',
            'RW' => 'RWANDA',
            'WS' => 'SAMOA',
            'SM' => 'SAN MARINO',
            'ST' => 'SÃO TOMÉ & PRÍNCIPE',
            'SA' => 'SAUDI ARABIA',
            'SN' => 'SENEGAL',
            'RS' => 'SERBIA',
            'SC' => 'SEYCHELLES',
            'SL' => 'SIERRA LEONE',
            'SG' => 'SINGAPORE',
            'SK' => 'SLOVAKIA',
            'SI' => 'SLOVENIA',
            'SB' => 'SOLOMON ISLANDS',
            'SO' => 'SOMALIA',
            'ZA' => 'SOUTH AFRICA',
            'KR' => 'SOUTH KOREA',
            'ES' => 'SPAIN',
            'LK' => 'SRI LANKA',
            'SH' => 'ST. HELENA',
            'KN' => 'ST. KITTS & NEVIS',
            'LC' => 'ST. LUCIA',
            'PM' => 'ST. PIERRE & MIQUELON',
            'VC' => 'ST. VINCENT & GRENADINES',
            'SR' => 'SURINAME',
            'SJ' => 'SVALBARD & JAN MAYEN',
            'SZ' => 'SWAZILAND',
            'SE' => 'SWEDEN',
            'CH' => 'SWITZERLAND',
            'TW' => 'TAIWAN',
            'TJ' => 'TAJIKISTAN',
            'TZ' => 'TANZANIA',
            'TH' => 'THAILAND',
            'TG' => 'TOGO',
            'TO' => 'TONGA',
            'TT' => 'TRINIDAD & TOBAGO',
            'TN' => 'TUNISIA',
            'TM' => 'TURKMENISTAN',
            'TC' => 'TURKS & CAICOS ISLANDS',
            'TV' => 'TUVALU',
            'UG' => 'UGANDA',
            'UA' => 'UKRAINE',
            'AE' => 'UNITED ARAB EMIRATES',
            'GB' => 'UNITED KINGDOM',
            'US' => 'UNITED STATES',
            'UY' => 'URUGUAY',
            'VU' => 'VANUATU',
            'VA' => 'VATICAN CITY',
            'VE' => 'VENEZUELA',
            'VN' => 'VIETNAM',
            'WF' => 'WALLIS & FUTUNA',
            'YE' => 'YEMEN',
            'ZM' => 'ZAMBIA',
            'ZW' => 'ZIMBABWE',
        ];
    }

    /**
     * Get the ISO currency codes supported by PayPal (IS0-4217)
     *
     * @return array
     */
    public function getCurrencyCodes()
    {
        return [
            'AUD' => [
                'name' => 'Australian dollar',
                'decimals' => true,
            ],
            'BRL' => [
                'name' => 'Brazilian real',
                'decimals' => true,
            ],
            'CAD' => [
                'name' => 'Canadian dollar',
                'decimals' => true,
            ],
            'CNY' => [
                'name' => 'Chinese Renmenbi',
                'decimals' => true,
            ],
            'CZK' => [
                'name' => 'Czech koruna',
                'decimals' => true,
            ],
            'DKK' => [
                'name' => 'Danish krone',
                'decimals' => true,
            ],
            'EUR' => [
                'name' => 'Euro',
                'decimals' => true,
            ],
            'HKD' => [
                'name' => 'Hong Kong dollar',
                'decimals' => true,
            ],
            'HUF' => [
                'name' => 'Hungarian forint',
                'decimals' => false,
            ],
            'INR' => [
                'name' => 'Indian rupee',
                'decimals' => true,
            ],
            'ILS' => [
                'name' => 'Israeli new shekel',
                'decimals' => true,
            ],
            'JPY' => [
                'name' => 'Japanese yen',
                'decimals' => false,
            ],
            'MYR' => [
                'name' => 'Malaysian ringgit',
                'decimals' => true,
            ],
            'MXN' => [
                'name' => 'Mexican peso',
                'decimals' => true,
            ],
            'TWD' => [
                'name' => 'New Taiwan dollar',
                'decimals' => false,
            ],
            'NZD' => [
                'name' => 'New Zealand dollar',
                'decimals' => true,
            ],
            'NOK' => [
                'name' => 'Norwegian krone',
                'decimals' => true,
            ],
            'PHP' => [
                'name' => 'Philippine peso',
                'decimals' => true,
            ],
            'PLN' => [
                'name' => 'Polish złoty',
                'decimals' => true,
            ],
            'GBP' => [
                'name' => 'Pound sterling',
                'decimals' => true,
            ],
            'RUB' => [
                'name' => 'Russian ruble',
                'decimals' => true,
            ],
            'SGD' => [
                'name' => 'Singapore dollar',
                'decimals' => true,
            ],
            'SEK' => [
                'name' => 'Swedish krona',
                'decimals' => true,
            ],
            'CHF' => [
                'name' => 'Swiss franc',
                'decimals' => true,
            ],
            'THB' => [
                'name' => 'Thai baht',
                'decimals' => true,
            ],
            'USD' => [
                'name' => 'United States dollar',
                'decimals' => true,
            ],
        ];
    }
}
