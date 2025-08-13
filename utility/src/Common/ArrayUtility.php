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

class ArrayUtility
{
    /**
     * Find missing keys between array of keys and reference keys.
     *
     * @param array $keysToCheck
     * @param array $referenceKeys
     *
     * @return array
     */
    public static function findMissingKeys(array $keysToCheck, array $referenceKeys): array
    {
        $missingKeys = [];

        foreach ($keysToCheck as $key) {
            if (!in_array(strtoupper($key), array_keys($referenceKeys))) {
                $missingKeys[] = $key;
            }
        }

        return $missingKeys;
    }

    /**
     * Recursively compares two arrays and returns the differences.
     *
     * @param array $array1
     * @param array $array2
     * @param int $maxDepth
     * @param int $currentDepth
     *
     * @return array
     */
    public static function arrayRecursiveDiff(array $array1, array $array2, $maxDepth = 5, $currentDepth = 0): array
    {
        $result = [];

        if ($currentDepth >= $maxDepth) {
            return $result;
        }

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiff = self::arrayRecursiveDiff($value, $array2[$key], $maxDepth, $currentDepth + 1);
                    if (!empty($recursiveDiff)) {
                        $result[$key] = $recursiveDiff;
                    }
                } elseif ($value !== $array2[$key]) {
                    $result[$key] = $value;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
