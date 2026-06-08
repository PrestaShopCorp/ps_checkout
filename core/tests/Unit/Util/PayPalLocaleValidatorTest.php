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

namespace Tests\Unit\PsCheckout\Core\Util;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Util\PayPalLocaleValidator;

class PayPalLocaleValidatorTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public function validLocaleProvider(): array
    {
        return [
            'en-US' => ['en-US'],
            'fr-FR' => ['fr-FR'],
            'de-DE' => ['de-DE'],
            'es-ES' => ['es-ES'],
            'it-IT' => ['it-IT'],
            'pt-BR' => ['pt-BR'],
            'pt-PT' => ['pt-PT'],
            'nl-NL' => ['nl-NL'],
            'pl-PL' => ['pl-PL'],
            'ru-RU' => ['ru-RU'],
            'zh-CN' => ['zh-CN'],
            'zh-HK' => ['zh-HK'],
            'zh-TW' => ['zh-TW'],
            'ja-JP' => ['ja-JP'],
            'ko-KR' => ['ko-KR'],
            'ar-EG' => ['ar-EG'],
            'fr-XC' => ['fr-XC'],
            'es-XC' => ['es-XC'],
            'zh-XC' => ['zh-XC'],
        ];
    }

    /**
     * @dataProvider validLocaleProvider
     */
    public function testIsSupportedReturnsTrueForValidLocales(string $locale): void
    {
        $this->assertTrue(PayPalLocaleValidator::isSupported($locale));
    }

    /**
     * @return array<string, array{string}>
     */
    public function invalidLocaleProvider(): array
    {
        return [
            'empty' => [''],
            'underscore format' => ['fr_FR'],
            'language only' => ['fr'],
            'unknown region' => ['xx-XX'],
            'en-GB locale' => ['en-FR'],
            'unsupported BCP-47' => ['zh-Hant-TW'],
            'numeric' => ['123'],
        ];
    }

    /**
     * @dataProvider invalidLocaleProvider
     */
    public function testIsSupportedReturnsFalseForInvalidLocales(string $locale): void
    {
        $this->assertFalse(PayPalLocaleValidator::isSupported($locale));
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function patternProvider(): array
    {
        return [
            'valid language only' => ['en', true],
            'valid language-region' => ['en-US', true],
            'valid language-script-region' => ['zh-Hant-TW', true],
            'valid language-numeric-region' => ['en-001', true],
            'underscore separator' => ['fr_FR', false],
            'uppercase language' => ['FR-FR', false],
            'lowercase region' => ['en-us', false],
            'too long language' => ['eng-US', false],
            'empty' => ['', false],
        ];
    }

    /**
     * @dataProvider patternProvider
     */
    public function testLocalePattern(string $locale, bool $expected): void
    {
        $this->assertSame($expected, preg_match(PayPalLocaleValidator::LOCALE_PATTERN, $locale) === 1);
    }

    public function testGetValidLocaleReturnsLocaleWhenSupported(): void
    {
        $this->assertSame('fr-FR', PayPalLocaleValidator::getValidLocale('fr-FR'));
    }

    public function testGetValidLocaleReturnsEmptyStringWhenNotSupported(): void
    {
        $this->assertSame('', PayPalLocaleValidator::getValidLocale('fr_FR'));
        $this->assertSame('', PayPalLocaleValidator::getValidLocale(''));
        $this->assertSame('', PayPalLocaleValidator::getValidLocale('xx-XX'));
    }
}
