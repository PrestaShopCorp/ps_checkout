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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * @see https://developer.paypal.com/api/rest/reference/country-codes/
 */
class PayPalCountryProvider
{
    const COUNTRIES = [
        ['iso_code' => 'AL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'DZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AD', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AR', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'AM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'AU', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'AT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'AZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BH', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BB', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BY', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'BE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'BZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BJ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'BO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BR', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'VG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'BG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BF', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'BI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KH', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'CM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CA', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'CV', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KY', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TD', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'C2', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'CO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CD', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CR', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'CI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'HR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CY', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'CZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'DK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'DJ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'DM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'DO', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'EC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'EG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SV', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'ER', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'EE', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'ET', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'FK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'FO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'FJ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'FI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'FR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'GF', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'PF', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'DE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'GI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'GL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'GD', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GP', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'GT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'GY', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'HN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'HK', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'HU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'IS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'IN', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'ID', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'IE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'IL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'IT', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'JM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'JP', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'JO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KZ', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'KE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'KG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'LA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'LV', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'LS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'LI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'LT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'LU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MY', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'MV', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'ML', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MH', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MQ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'YT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MX', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'FM', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'MD', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MN', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'ME', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'MA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'MZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NP', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'NL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'NC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'NI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NG', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'NU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NF', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'NO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'OM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'PW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'PA', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'PG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'PY', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'PE', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'PH', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'PN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'PL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'PT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'QA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'RE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'RO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'RU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'RW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'WS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'ST', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'RS', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SL', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SG', 'isCityRequired' => false, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'SK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SI', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SB', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'ZA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'KR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'ES', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'LK', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'SH', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'KN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'LC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'PM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'VC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SR', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SJ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'SE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'CH', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'TW', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'TJ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TZ', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TH', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'TG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TO', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TT', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TN', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TC', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'TV', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'UG', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'UA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'AE', 'isCityRequired' => false, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'GB', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'US', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'UY', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => true],
        ['iso_code' => 'VU', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'VA', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => true],
        ['iso_code' => 'VE', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'VN', 'isCityRequired' => true, 'isStateRequired' => true, 'isZipCodeRequired' => false],
        ['iso_code' => 'WF', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'YE', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'ZM', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
        ['iso_code' => 'ZW', 'isCityRequired' => true, 'isStateRequired' => false, 'isZipCodeRequired' => false],
    ];

    /**
     * @return string[]
     */
    public function getSupportedCountryCode()
    {
        return array_column(static::COUNTRIES, 'iso_code');
    }

    /**
     * @param string $code
     *
     * @return PayPalCountry
     *
     * @throws PsCheckoutException
     */
    public function getByCode($code)
    {
        foreach (static::COUNTRIES as $country) {
            if (0 === strcasecmp($code, $country['iso_code'])) {
                return PayPalCountry::fromArray($country);
            }
        }

        throw new PsCheckoutException(sprintf('Unsupported country code, given %s', var_export($code, true)), PsCheckoutException::UNSUPPORTED_COUNTRY);
    }
}
