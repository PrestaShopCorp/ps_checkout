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
use PsCheckout\Utility\Common\NumberUtility;

class NumberUtilityTest extends TestCase
{
    /**
     * Data provider for testFormatAmount
     *
     * @return array
     */
    public function amountProvider(): array
    {
        return [
            'USD with 2 decimals' => [1234.56, 'USD', '1234.56'],
            'EUR with 2 decimals' => [99.99, 'EUR', '99.99'],
            'GBP with 2 decimals' => ['45.50', 'GBP', '45.50'],
            'JPY with 0 decimals' => [150, 'JPY', '150'],
            'HUF with 0 decimals' => [99, 'HUF', '99'],
            'TWD with 0 decimals' => ['77', 'TWD', '77'],
            'Negative USD' => [-45.67, 'USD', '-45.67'],
            'Zero amount' => [0, 'USD', '0.00'],
            'Large number' => [123456789.12, 'EUR', '123456789.12'],
            'Small fractional number' => [0.009, 'USD', '0.01'],
            'Whole number with decimals' => [100.00, 'EUR', '100.00'],
            'String integer input' => ['200', 'USD', '200.00'],
            'String float input' => ['150.75', 'EUR', '150.75'],
            'Rounded down JPY' => [150.4, 'JPY', '150'],
            'Rounded up HUF' => [199.6, 'HUF', '200'],
        ];
    }

    /**
     * @dataProvider amountProvider
     */
    public function testFormatAmount($amount, $isoCode, $expected)
    {
        $this->assertSame($expected, NumberUtility::formatAmount($amount, $isoCode));
    }
}
