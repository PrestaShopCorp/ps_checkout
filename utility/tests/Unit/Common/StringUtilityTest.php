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
use PsCheckout\Utility\Common\StringUtility;

class StringUtilityTest extends TestCase
{
    /**
     * Data provider for testTruncate
     *
     * @return array
     */
    public function truncateProvider(): array
    {
        return [
            'Basic truncation' => ['Hello World', 5, 'Hello'],
            'Exact limit' => ['Testing', 7, 'Testing'],
            'Longer string' => ['This is a long sentence.', 10, 'This is a '],
            'Empty string' => ['', 5, ''],
            'Zero limit' => ['Non-empty', 0, ''],
            'Unicode characters' => ['Привет мир', 6, 'Привет'],
            'Multibyte emoji' => ['Hello 😊', 6, 'Hello '],
            'Negative limit' => ['Should not truncate', -5, 'Should not truncate'],
            'Single character' => ['A', 1, 'A'],
            'Limit greater than string' => ['Short', 10, 'Short'],
            'String with spaces' => ['   Leading space', 5, '   Le'],
        ];
    }

    /**
     * @dataProvider truncateProvider
     */
    public function testTruncate($input, $limit, $expected)
    {
        $this->assertSame($expected, StringUtility::truncate($input, $limit));
    }
}
