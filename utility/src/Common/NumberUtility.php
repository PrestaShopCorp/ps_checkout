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

class NumberUtility
{
    /**
     * @param float|int|string $amount
     * @param string $isoCode
     *
     * @return string
     */
    public static function formatAmount($amount, string $isoCode): string
    {
        return sprintf('%01.' . self::getNbDecimalToRound($isoCode) . 'F', $amount);
    }

    /**
     * Get decimal to round correspondent to the payment currency used
     * Advise from PayPal: Always round to 2 decimals except for HUF, JPY and TWD
     * currencies which require a round with 0 decimal
     *
     * @return int
     */
    private static function getNbDecimalToRound($isoCode): int
    {
        if (in_array($isoCode, ['HUF', 'JPY', 'TWD'], true)) {
            return 0;
        }

        return 2;
    }
}
