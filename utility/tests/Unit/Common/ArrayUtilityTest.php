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

namespace Unit\Common;

use PHPUnit\Framework\TestCase;
use PsCheckout\Utility\Common\ArrayUtility;

class ArrayUtilityTest extends TestCase
{
    /**
     * @dataProvider keysProvider
     */
    public function testFindMissingKeys($keysToCheck, $referenceKeys, $expectedResult)
    {
        $result = ArrayUtility::findMissingKeys($keysToCheck, $referenceKeys);
        $this->assertEquals($expectedResult, $result);
    }

    public function keysProvider(): array
    {
        return [
            'no missing keys' => [
                'keysToCheck' => ['KEY1', 'KEY2'],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => [],
            ],
            'some missing keys' => [
                'keysToCheck' => ['KEY1', 'KEY3'],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => ['KEY3'],
            ],
            'all keys missing' => [
                'keysToCheck' => ['KEY3', 'KEY4'],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => ['KEY3', 'KEY4'],
            ],
            'case insensitivity' => [
                'keysToCheck' => ['key1', 'key2'],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => [],
            ],
            'mixed case keys' => [
                'keysToCheck' => ['Key1', 'key2'],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => [],
            ],
            'empty keys to check' => [
                'keysToCheck' => [],
                'referenceKeys' => ['KEY1' => 'value1', 'KEY2' => 'value2'],
                'expectedResult' => [],
            ],
            'empty reference keys' => [
                'keysToCheck' => ['KEY1', 'KEY2'],
                'referenceKeys' => [],
                'expectedResult' => ['KEY1', 'KEY2'],
            ],
            'null values in reference keys' => [
                'keysToCheck' => ['KEY1', 'KEY2'],
                'referenceKeys' => ['KEY1' => null, 'KEY2' => null],
                'expectedResult' => [],
            ],
        ];
    }

    /**
     * @dataProvider arrayRecursiveDiffProvider
     */
    public function testArrayRecursiveDiff($array1, $array2, $maxDepth, $expected)
    {
        $this->assertEquals($expected, ArrayUtility::arrayRecursiveDiff($array1, $array2, $maxDepth));
    }

    public function arrayRecursiveDiffProvider()
    {
        return [
            'different values' => [
                ['a' => 1, 'b' => 2, 'c' => ['d' => 3, 'e' => 4]],
                ['a' => 1, 'b' => 3, 'c' => ['d' => 3, 'e' => 5]],
                5,
                ['b' => 2, 'c' => ['e' => 4]],
            ],
            'missing keys' => [
                ['a' => 1, 'b' => 2, 'c' => ['d' => 3]],
                ['a' => 1],
                5,
                ['b' => 2, 'c' => ['d' => 3]],
            ],
            'nested arrays' => [
                ['a' => ['b' => ['c' => 1, 'd' => 2]]],
                ['a' => ['b' => ['c' => 1, 'd' => 3]]],
                5,
                ['a' => ['b' => ['d' => 2]]],
            ],
            'max depth exceeded' => [
                ['a' => ['b' => ['c' => ['d' => 1]]]],
                ['a' => ['b' => ['c' => ['d' => 2]]]],
                2,
                [],
            ],
            'identical arrays' => [
                ['a' => 1, 'b' => 2, 'c' => ['d' => 3]],
                ['a' => 1, 'b' => 2, 'c' => ['d' => 3]],
                5,
                [],
            ],
            'empty arrays' => [
                [],
                [],
                5,
                [],
            ],
            'one empty array' => [
                ['a' => 1, 'b' => 2],
                [],
                5,
                ['a' => 1, 'b' => 2],
            ],
            'non-associative arrays' => [
                [1, 2, 3],
                [1, 2, 4],
                5,
                [2 => 3],
            ],
            'deeply nested beyond max depth' => [
                ['a' => ['b' => ['c' => ['d' => ['e' => 1]]]]],
                ['a' => ['b' => ['c' => ['d' => ['e' => 2]]]]],
                3,
                [],
            ],
        ];
    }
}
