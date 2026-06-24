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

namespace PsCheckout\Utility\Common;

class StringUtility
{
    /**
     * @param string $str
     * @param int $limit
     *
     * @return string
     */
    public static function truncate(string $str, int $limit): string
    {
        if (empty($str) || $limit < 0) {
            return $str;
        }

        return mb_substr($str, 0, $limit);
    }

    /**
     * Normalize a PayPal brand_name value: strip control characters (pattern ^.*$ forbids newlines)
     * and truncate to the 127-character API limit.
     *
     * @param string $name
     *
     * @return string
     */
    public static function normalizeBrandName(string $name): string
    {
        $normalized = preg_replace('/[\x00-\x1F\x7F]/u', '', $name);

        return self::truncate((string) $normalized, 127);
    }
}
