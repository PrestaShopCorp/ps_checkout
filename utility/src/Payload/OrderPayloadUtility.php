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

class OrderPayloadUtility
{
    /**
     * @param \Address $address
     * @param string $countryIso
     * @param string $stateName
     *
     * @return array
     */
    public static function getAddressPortable(\Address $address, string $countryIso, string $stateName): array
    {
        return array_filter([
            'address_line_1' => $address->address1,
            'address_line_2' => $address->address2,
            'admin_area_1' => $stateName,
            'admin_area_2' => $address->city,
            'country_code' => PaypalCountryCodeUtility::getPaypalIsoCode(strtoupper($countryIso)),
            'postal_code' => $address->postcode,
        ]);
    }

    /**
     * Compares two PayPal amount arrays with normalized numeric values.
     * Structure: ['currency_code' => 'USD', 'value' => '10.00', 'breakdown' => [...]]
     * Normalizes 'value' properties to 2 decimal places before comparison.
     * Ignores breakdown properties that don't exist in one array but have '0.00' value in the other.
     *
     * @param array $amount1 The first amount array (e.g., PayPal response)
     * @param array $amount2 The second amount array (e.g., new payload)
     *
     * @return array Differences found between amounts
     */
    public static function amountWithBreakdownDiff(array $amount1, array $amount2): array
    {
        $diff = [];

        // Compare currency_code
        if ($amount1['currency_code'] !== $amount2['currency_code']) {
            $diff['currency_code'] = $amount1['currency_code'];
        }

        // Compare value (normalized)
        if (!self::areNumericValuesEqual($amount1['value'], $amount2['value'])) {
            $diff['value'] = $amount1['value'];
        }

        // Compare breakdown items
        $breakdown1 = $amount1['breakdown'] ?? [];
        $breakdown2 = $amount2['breakdown'] ?? [];

        if (!empty($breakdown1) || !empty($breakdown2)) {
            $breakdownDiff = self::compareBreakdownItems($breakdown1, $breakdown2);
            if (!empty($breakdownDiff)) {
                $diff['breakdown'] = $breakdownDiff;
            }
        }

        return $diff;
    }

    /**
     * Compares breakdown items (item_total, shipping, tax_total, etc.).
     * Each item has structure: ['currency_code' => 'USD', 'value' => '10.00']
     *
     * @param array $breakdown1 First breakdown array
     * @param array $breakdown2 Second breakdown array
     *
     * @return array Differences found in breakdown
     */
    private static function compareBreakdownItems(array $breakdown1, array $breakdown2): array
    {
        $diff = [];

        // Get all unique keys from both breakdowns
        $allKeys = array_unique(array_merge(array_keys($breakdown1), array_keys($breakdown2)));

        foreach ($allKeys as $key) {
            $item1 = $breakdown1[$key] ?? null;
            $item2 = $breakdown2[$key] ?? null;

            // If item exists in only one array
            if ($item1 === null) {
                // Item only in breakdown2 - ignore if zero value
                if (!self::isZeroValueItem($item2)) {
                    $diff[$key] = $item2;
                }

                continue;
            }

            if ($item2 === null) {
                // Item only in breakdown1 - ignore if zero value
                if (!self::isZeroValueItem($item1)) {
                    $diff[$key] = $item1;
                }

                continue;
            }

            // Both items exist - compare them
            $itemDiff = [];

            // Compare currency_code
            if ($item1['currency_code'] !== $item2['currency_code']) {
                $itemDiff['currency_code'] = $item1['currency_code'];
            }

            // Compare value (normalized)
            if (!self::areNumericValuesEqual($item1['value'], $item2['value'])) {
                $itemDiff['value'] = $item1['value'];
            }

            if (!empty($itemDiff)) {
                $diff[$key] = $itemDiff;
            }
        }

        return $diff;
    }

    /**
     * Compares two numeric values after normalizing to 2 decimal places.
     *
     * @param mixed $value1
     * @param mixed $value2
     *
     * @return bool True if values are equal after normalization
     */
    private static function areNumericValuesEqual($value1, $value2): bool
    {
        if ($value1 === null && $value2 === null) {
            return true;
        }

        if (!is_numeric($value1) || !is_numeric($value2)) {
            return $value1 === $value2;
        }

        $normalized1 = number_format((float) $value1, 2, '.', '');
        $normalized2 = number_format((float) $value2, 2, '.', '');

        return $normalized1 === $normalized2;
    }

    /**
     * Checks if an item has a zero value.
     *
     * @param array|null $item The breakdown item
     *
     * @return bool True if item has value '0.00'
     */
    private static function isZeroValueItem($item): bool
    {
        if (!is_array($item) || !isset($item['value'])) {
            return false;
        }

        if (!is_numeric($item['value'])) {
            return false;
        }

        return number_format((float) $item['value'], 2, '.', '') === '0.00';
    }
}
