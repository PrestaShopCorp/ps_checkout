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

namespace PrestaShop\Module\PrestashopCheckout\Country\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;

class CountryCode
{
    const COUNTRY_CODE_AVAILABLE = [
        'AL',
        'DZ',
        'AD',
        'AO',
        'AI',
        'AG',
        'AR',
        'AM',
        'AW',
        'AU',
        'AT',
        'AZ',
        'BS',
        'BH',
        'BB',
        'BY',
        'BE',
        'BZ',
        'BJ',
        'BM',
        'BT',
        'BO',
        'BA',
        'BW',
        'BR',
        'VG',
        'BN',
        'BG',
        'BF',
        'BI',
        'KH',
        'CM',
        'CA',
        'CV',
        'KY',
        'TD',
        'CL',
        'C2',
        'CO',
        'KM',
        'CG',
        'CD',
        'CK',
        'CR',
        'CI',
        'HR',
        'CY',
        'CZ',
        'DK',
        'DJ',
        'DM',
        'DO',
        'EC',
        'EG',
        'SV',
        'ER',
        'EE',
        'ET',
        'FK',
        'FO',
        'FJ',
        'FI',
        'FR',
        'GF',
        'PF',
        'GA',
        'GM',
        'GE',
        'DE',
        'GI',
        'GR',
        'GL',
        'GD',
        'GP',
        'GT',
        'GN',
        'GW',
        'GY',
        'HN',
        'HK',
        'HU',
        'IS',
        'IN',
        'ID',
        'IE',
        'IL',
        'IT',
        'JM',
        'JP',
        'JO',
        'KZ',
        'KE',
        'KI',
        'KW',
        'KG',
        'LA',
        'LV',
        'LS',
        'LI',
        'LT',
        'LU',
        'MK',
        'MG',
        'MW',
        'MY',
        'MV',
        'ML',
        'MT',
        'MH',
        'MQ',
        'MR',
        'MU',
        'YT',
        'MX',
        'FM',
        'MD',
        'MC',
        'MN',
        'ME',
        'MS',
        'MA',
        'MZ',
        'NA',
        'NR',
        'NP',
        'NL',
        'NC',
        'NZ',
        'NI',
        'NE',
        'NG',
        'NU',
        'NF',
        'NO',
        'OM',
        'PW',
        'PA',
        'PG',
        'PY',
        'PE',
        'PH',
        'PN',
        'PL',
        'PT',
        'QA',
        'RE',
        'RO',
        'RU',
        'RW',
        'WS',
        'SM',
        'ST',
        'SA',
        'SN',
        'RS',
        'SC',
        'SL',
        'SG',
        'SK',
        'SI',
        'SB',
        'SO',
        'ZA',
        'KR',
        'ES',
        'LK',
        'SH',
        'KN',
        'LC',
        'PM',
        'VC',
        'SR',
        'SJ',
        'SZ',
        'SE',
        'CH',
        'TW',
        'TJ',
        'TZ',
        'TH',
        'TG',
        'TO',
        'TT',
        'TN',
        'TM',
        'TC',
        'TV',
        'UG',
        'UA',
        'AE',
        'GB',
        'UK', // TODO choose between GB and UK ?
        'US',
        'UY',
        'VU',
        'VA',
        'VE',
        'VN',
        'WF',
        'YE',
        'ZM',
        'ZW',
    ];

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @param string $countryCode
     *
     * @throws CountryException
     */
    public function __construct($countryCode)
    {
        $this->countryCode = $this->assertCountryCodeIsValid($countryCode);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->countryCode;
    }

    /**
     * @param $countryCode
     *
     * @return string
     *
     * @throws CountryException
     */
    public function assertCountryCodeIsValid($countryCode)
    {
        if (!is_string($countryCode)) {
            throw new CountryException(sprintf('CODE is not a string (%s)', gettype($countryCode)), CountryException::WRONG_TYPE_CODE);
        }
        if (!in_array($countryCode, self::COUNTRY_CODE_AVAILABLE)) {
            throw new CountryException("Invalid code ($countryCode)", CountryException::INVALID_CODE);
        }

        return $countryCode;
    }
}
