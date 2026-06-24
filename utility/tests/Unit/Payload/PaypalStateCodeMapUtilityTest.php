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

namespace Unit\Payload;

use PHPUnit\Framework\TestCase;
use PsCheckout\Utility\Payload\PaypalStateCodeMapUtility;

class PaypalStateCodeMapUtilityTest extends TestCase
{
    /**
     * @dataProvider provideMappedCodes
     */
    public function testGetPaypalStateCodeReturnsMappedValue(
        string $countryCode,
        string $psStateCode,
        string $expected
    ): void {
        $this->assertSame($expected, PaypalStateCodeMapUtility::getPaypalStateCode($countryCode, $psStateCode));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public function provideMappedCodes(): array
    {
        return [
            // Japan: JIS numeric → PayPal string
            'JP 01 Hokkaido'   => ['JP', '01', 'HOKKAIDO'],
            'JP 13 Tokyo'      => ['JP', '13', 'TOKYO-TO'],
            'JP 26 Kyoto'      => ['JP', '26', 'KYOTO-FU'],
            'JP 27 Osaka'      => ['JP', '27', 'OSAKA-FU'],
            'JP 23 Aichi'      => ['JP', '23', 'AICHI-KEN'],
            'JP 47 Okinawa'    => ['JP', '47', 'OKINAWA-KEN'],

            // Mexico: mismatched PS iso_code → PayPal code
            'MX BCN → BC'      => ['MX', 'BCN', 'BC'],
            'MX ROO → Q ROO'   => ['MX', 'ROO', 'Q ROO'],
            'MX CMX → CDMX'    => ['MX', 'CMX', 'CDMX'],
            'MX NLE → NL'      => ['MX', 'NLE', 'NL'],
            'MX TLA → TLAX'    => ['MX', 'TLA', 'TLAX'],

            // Argentina: single-letter iso_code → uppercase full name
            'AR B Buenos Aires'     => ['AR', 'B', 'BUENOS AIRES'],
            'AR C CABA'             => ['AR', 'C', 'CIUDAD AUTÓNOMA DE BUENOS AIRES'],
            'AR X Córdoba'          => ['AR', 'X', 'CÓRDOBA'],
            'AR Q Neuquén'          => ['AR', 'Q', 'NEUQUÉN'],
            'AR T Tucumán'          => ['AR', 'T', 'TUCUMÁN'],
        ];
    }

    /**
     * @dataProvider providePassthroughCodes
     */
    public function testGetPaypalStateCodeReturnsUnchangedWhenNotMapped(
        string $countryCode,
        string $psStateCode
    ): void {
        $this->assertSame($psStateCode, PaypalStateCodeMapUtility::getPaypalStateCode($countryCode, $psStateCode));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function providePassthroughCodes(): array
    {
        return [
            'CA ON (already correct)'  => ['CA', 'ON'],
            'CA BC (already correct)'  => ['CA', 'BC'],
            'US CA (already correct)'  => ['US', 'CA'],
            'MX JAL (already correct)' => ['MX', 'JAL'],
            'JP 99 (unknown JIS)'      => ['JP', '99'],
            'FR some-state'            => ['FR', 'some-state'],
            'DE Bayern'                => ['DE', 'Bayern'],
            'empty country'            => ['', 'ON'],
        ];
    }

    public function testGetPaypalStateCodeIsCaseInsensitiveForCountry(): void
    {
        $this->assertSame('TOKYO-TO', PaypalStateCodeMapUtility::getPaypalStateCode('jp', '13'));
        $this->assertSame('BC', PaypalStateCodeMapUtility::getPaypalStateCode('mx', 'BCN'));
        $this->assertSame('BUENOS AIRES', PaypalStateCodeMapUtility::getPaypalStateCode('ar', 'B'));
    }

    /**
     * @dataProvider provideReverseMappedCodes
     */
    public function testGetShopStateCodeReturnsMappedPsIsoCode(
        string $countryCode,
        string $paypalStateCode,
        string $expected
    ): void {
        $this->assertSame($expected, PaypalStateCodeMapUtility::getShopStateCode($countryCode, $paypalStateCode));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public function provideReverseMappedCodes(): array
    {
        return [
            // Japan: PayPal string → JIS numeric
            'JP HOKKAIDO → 01'   => ['JP', 'HOKKAIDO', '01'],
            'JP TOKYO-TO → 13'   => ['JP', 'TOKYO-TO', '13'],
            'JP OSAKA-FU → 27'   => ['JP', 'OSAKA-FU', '27'],
            'JP OKINAWA-KEN → 47' => ['JP', 'OKINAWA-KEN', '47'],

            // Mexico: PayPal code → PS iso_code
            'MX BC → BCN'       => ['MX', 'BC', 'BCN'],
            'MX CDMX → CMX'     => ['MX', 'CDMX', 'CMX'],
            'MX Q ROO → ROO'    => ['MX', 'Q ROO', 'ROO'],
            'MX NL → NLE'       => ['MX', 'NL', 'NLE'],

            // Argentina: full name → single-letter
            'AR BUENOS AIRES → B'              => ['AR', 'BUENOS AIRES', 'B'],
            'AR CIUDAD AUTÓNOMA... → C'        => ['AR', 'CIUDAD AUTÓNOMA DE BUENOS AIRES', 'C'],
            'AR CÓRDOBA → X'                   => ['AR', 'CÓRDOBA', 'X'],
        ];
    }

    /**
     * @dataProvider provideReversePassthroughCodes
     */
    public function testGetShopStateCodeReturnsUnchangedWhenNotMapped(
        string $countryCode,
        string $paypalStateCode
    ): void {
        $this->assertSame($paypalStateCode, PaypalStateCodeMapUtility::getShopStateCode($countryCode, $paypalStateCode));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function provideReversePassthroughCodes(): array
    {
        return [
            'US CA (no country map)'  => ['US', 'CA'],
            'CA ON (no country map)'  => ['CA', 'ON'],
            'FR some-state'           => ['FR', 'some-state'],
            'JP UNKNOWN (unmapped)'   => ['JP', 'UNKNOWN-KEN'],
        ];
    }

    public function testGetShopStateCodeIsCaseInsensitiveForCountry(): void
    {
        $this->assertSame('01', PaypalStateCodeMapUtility::getShopStateCode('jp', 'HOKKAIDO'));
        $this->assertSame('CMX', PaypalStateCodeMapUtility::getShopStateCode('mx', 'CDMX'));
        $this->assertSame('B', PaypalStateCodeMapUtility::getShopStateCode('ar', 'BUENOS AIRES'));
    }
}
