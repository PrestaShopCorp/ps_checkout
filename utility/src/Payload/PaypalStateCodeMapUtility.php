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

namespace PsCheckout\Utility\Payload;

class PaypalStateCodeMapUtility
{
    /**
     * Per-country maps from PrestaShop ps_state.iso_code to PayPal admin_area_1 code.
     * Source: docs/state-and-province-codes.md cross-referenced with docs/ps_state.csv
     *
     * @var array<string, array<string, string>>
     */
    private static $map = [
        'JP' => [
            '01' => 'HOKKAIDO',       '02' => 'AOMORI-KEN',    '03' => 'IWATE-KEN',
            '04' => 'MIYAGI-KEN',     '05' => 'AKITA-KEN',     '06' => 'YAMAGATA-KEN',
            '07' => 'FUKUSHIMA-KEN',  '08' => 'IBARAKI-KEN',   '09' => 'TOCHIGI-KEN',
            '10' => 'GUNMA-KEN',      '11' => 'SAITAMA-KEN',   '12' => 'CHIBA-KEN',
            '13' => 'TOKYO-TO',       '14' => 'KANAGAWA-KEN',  '15' => 'NIIGATA-KEN',
            '16' => 'TOYAMA-KEN',     '17' => 'ISHIKAWA-KEN',  '18' => 'FUKUI-KEN',
            '19' => 'YAMANASHI-KEN',  '20' => 'NAGANO-KEN',    '21' => 'GIFU-KEN',
            '22' => 'SHIZUOKA-KEN',   '23' => 'AICHI-KEN',     '24' => 'MIE-KEN',
            '25' => 'SHIGA-KEN',      '26' => 'KYOTO-FU',      '27' => 'OSAKA-FU',
            '28' => 'HYOGO-KEN',      '29' => 'NARA-KEN',      '30' => 'WAKAYAMA-KEN',
            '31' => 'TOTTORI-KEN',    '32' => 'SHIMANE-KEN',   '33' => 'OKAYAMA-KEN',
            '34' => 'HIROSHIMA-KEN',  '35' => 'YAMAGUCHI-KEN', '36' => 'TOKUSHIMA-KEN',
            '37' => 'KAGAWA-KEN',     '38' => 'EHIME-KEN',     '39' => 'KOCHI-KEN',
            '40' => 'FUKUOKA-KEN',    '41' => 'SAGA-KEN',      '42' => 'NAGASAKI-KEN',
            '43' => 'KUMAMOTO-KEN',   '44' => 'OITA-KEN',      '45' => 'MIYAZAKI-KEN',
            '46' => 'KAGOSHIMA-KEN',  '47' => 'OKINAWA-KEN',
        ],
        'MX' => [
            'BCN' => 'BC',    'CAM' => 'CAMP',  'CHP' => 'CHIS',  'CHH' => 'CHIH',
            'COA' => 'COAH',  'CMX' => 'CDMX',  'DUR' => 'DGO',   'GUA' => 'GTO',
            'HID' => 'HGO',   'MIC' => 'MICH',  'NLE' => 'NL',    'QUE' => 'QRO',
            'ROO' => 'Q ROO', 'TAM' => 'TAMPS', 'TLA' => 'TLAX',
        ],
        'AR' => [
            'B' => 'BUENOS AIRES',
            'C' => 'CIUDAD AUTÓNOMA DE BUENOS AIRES',
            'K' => 'CATAMARCA',
            'H' => 'CHACO',
            'U' => 'CHUBUT',
            'X' => 'CÓRDOBA',
            'W' => 'CORRIENTES',
            'E' => 'ENTRE RÍOS',
            'P' => 'FORMOSA',
            'Y' => 'JUJUY',
            'L' => 'LA PAMPA',
            'F' => 'LA RIOJA',
            'M' => 'MENDOZA',
            'N' => 'MISIONES',
            'Q' => 'NEUQUÉN',
            'R' => 'RÍO NEGRO',
            'A' => 'SALTA',
            'J' => 'SAN JUAN',
            'D' => 'SAN LUIS',
            'Z' => 'SANTA CRUZ',
            'S' => 'SANTA FE',
            'G' => 'SANTIAGO DEL ESTERO',
            'V' => 'TIERRA DEL FUEGO',
            'T' => 'TUCUMÁN',
        ],
    ];

    /**
     * Returns the PayPal-required state/province code for the given country and PrestaShop
     * ps_state.iso_code. Returns $stateCode unchanged if no mapping is defined.
     *
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     * @param string $stateCode   Value from ps_state.iso_code (via StateRepository::getIsoById)
     *
     * @return string
     */
    public static function getPaypalStateCode(string $countryCode, string $stateCode): string
    {
        $countryCode = strtoupper($countryCode);
        if (isset(self::$map[$countryCode][$stateCode])) {
            return self::$map[$countryCode][$stateCode];
        }

        return $stateCode;
    }
}
