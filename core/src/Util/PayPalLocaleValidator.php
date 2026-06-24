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

namespace PsCheckout\Core\Util;

class PayPalLocaleValidator
{
    const LOCALE_PATTERN = '/^[a-z]{2}(?:-[A-Z][a-z]{3})?(?:-(?:[A-Z]{2}|[0-9]{3}))?$/';

    /**
     * PayPal-supported locale codes in BCP-47 format for REST APIs.
     *
     * @var string[]
     */
    private static $supportedLocales = [
        'ar-EG',
        'cs-CZ',
        'da-DK',
        'de-DE',
        'el-GR',
        'en-AU',
        'en-GB',
        'en-IN',
        'en-US',
        'es-ES',
        'es-XC',
        'fi-FI',
        'fr-CA',
        'fr-FR',
        'fr-XC',
        'he-IL',
        'hu-HU',
        'id-ID',
        'it-IT',
        'ja-JP',
        'ko-KR',
        'nl-NL',
        'no-NO',
        'pl-PL',
        'pt-BR',
        'pt-PT',
        'ru-RU',
        'sk-SK',
        'sv-SE',
        'th-TH',
        'zh-CN',
        'zh-HK',
        'zh-TW',
        'zh-XC',
    ];

    public static function isSupported(string $locale): bool
    {
        return preg_match(self::LOCALE_PATTERN, $locale) === 1
            && in_array($locale, self::$supportedLocales, true);
    }

    /**
     * Returns the locale if it is a PayPal-supported BCP-47 locale code, or an empty string otherwise.
     */
    public static function getValidLocale(string $locale): string
    {
        return self::isSupported($locale) ? $locale : '';
    }
}
