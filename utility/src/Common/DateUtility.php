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

class DateUtility
{
    /**
     * Utility class for formatting dates with timezones.
     *
     * @param string $timestamp 2025-01-16 12:00:00
     * @param string $format Y-m-d H:i:s
     * @param string $timeZone America/New_York
     *
     * @return string
     */
    public static function formatDate(string $timestamp, string $format = 'Y-m-d H:i:s', string $timeZone = ''): string
    {
        if (!is_string($timestamp) || !strtotime($timestamp)) {
            throw new \InvalidArgumentException("Invalid timestamp provided: $timestamp");
        }

        try {
            $timezoneToUse = !empty($timeZone) ? new \DateTimeZone($timeZone) : new \DateTimeZone('UTC');
            $date = new \DateTime($timestamp, $timezoneToUse);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Could not create a DateTime object from timestamp: $timestamp", 0, $e);
        }

        // Format the date
        return $date->format($format);
    }
}
