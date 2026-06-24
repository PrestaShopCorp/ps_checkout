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
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;

class PaypalAddressRequirementsUtilityTest extends TestCase
{
    /**
     * @dataProvider providePostalCodeRequired
     */
    public function testIsPostalCodeRequiredReturnsTrue(string $countryCode): void
    {
        $this->assertTrue(PaypalAddressRequirementsUtility::isPostalCodeRequired($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function providePostalCodeRequired(): array
    {
        return [
            'US'  => ['US'],
            'GB'  => ['GB'],
            'DE'  => ['DE'],
            'FR'  => ['FR'],
            'JP'  => ['JP'],
            'AU'  => ['AU'],
            'CA'  => ['CA'],
            'BR'  => ['BR'],
            'CH'  => ['CH'],
            'SE'  => ['SE'],
            'NL'  => ['NL'],
            'PL'  => ['PL'],
            'IT'  => ['IT'],
            'SG'  => ['SG'],
            'C2'  => ['C2'],
            'CN'  => ['CN'],
        ];
    }

    /**
     * @dataProvider providePostalCodeOptional
     */
    public function testIsPostalCodeRequiredReturnsFalse(string $countryCode): void
    {
        $this->assertFalse(PaypalAddressRequirementsUtility::isPostalCodeRequired($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function providePostalCodeOptional(): array
    {
        return [
            'IE (Ireland)'     => ['IE'],
            'HK (Hong Kong)'   => ['HK'],
            'AE (UAE)'         => ['AE'],
            'ZA (South Africa)'=> ['ZA'],
            'NG (Nigeria)'     => ['NG'],
        ];
    }

    /**
     * @dataProvider provideCityRequired
     */
    public function testIsCityRequiredReturnsTrue(string $countryCode): void
    {
        $this->assertTrue(PaypalAddressRequirementsUtility::isCityRequired($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideCityRequired(): array
    {
        return [
            'US'  => ['US'],
            'FR'  => ['FR'],
            'DE'  => ['DE'],
            'GB'  => ['GB'],
            'IE'  => ['IE'],
            'AU'  => ['AU'],
        ];
    }

    /**
     * @dataProvider provideCityOptional
     */
    public function testIsCityRequiredReturnsFalse(string $countryCode): void
    {
        $this->assertFalse(PaypalAddressRequirementsUtility::isCityRequired($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideCityOptional(): array
    {
        return [
            'HK (Hong Kong)'  => ['HK'],
            'JP (Japan)'      => ['JP'],
            'SG (Singapore)'  => ['SG'],
        ];
    }

    public function testIsCityRequiredIsCaseInsensitive(): void
    {
        $this->assertFalse(PaypalAddressRequirementsUtility::isCityRequired('hk'));
        $this->assertFalse(PaypalAddressRequirementsUtility::isCityRequired('jp'));
        $this->assertFalse(PaypalAddressRequirementsUtility::isCityRequired('sg'));
    }

    public function testIsPostalCodeRequiredIsCaseInsensitive(): void
    {
        $this->assertTrue(PaypalAddressRequirementsUtility::isPostalCodeRequired('us'));
        $this->assertTrue(PaypalAddressRequirementsUtility::isPostalCodeRequired('gb'));
        $this->assertFalse(PaypalAddressRequirementsUtility::isPostalCodeRequired('ie'));
    }

    /**
     * @dataProvider provideStateIsoCodeCountries
     */
    public function testUsesStateIsoCodeReturnsTrue(string $countryCode): void
    {
        $this->assertTrue(PaypalAddressRequirementsUtility::usesStateIsoCode($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideStateIsoCodeCountries(): array
    {
        return [
            'US'           => ['US'],
            'CA (Canada)'  => ['CA'],
            'BR (Brazil)'  => ['BR'],
            'IT (Italy)'   => ['IT'],
            'MX (Mexico)'  => ['MX'],
            'JP (Japan)'   => ['JP'],
            'CN (China)'   => ['CN'],
            'C2 (China PayPal code)' => ['C2'],
            'ID (Indonesia)' => ['ID'],
            'AR (Argentina)' => ['AR'],
            'lowercase us' => ['us'],
        ];
    }

    /**
     * @dataProvider provideStateNameCountries
     */
    public function testUsesStateIsoCodeReturnsFalse(string $countryCode): void
    {
        $this->assertFalse(PaypalAddressRequirementsUtility::usesStateIsoCode($countryCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideStateNameCountries(): array
    {
        return [
            'IN (India — full names)'    => ['IN'],
            'TH (Thailand — full names)' => ['TH'],
            'FR'                         => ['FR'],
            'DE'                          => ['DE'],
        ];
    }
}
