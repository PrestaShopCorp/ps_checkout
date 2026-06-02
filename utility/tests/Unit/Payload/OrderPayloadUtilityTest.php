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

namespace Unit\Payload;

use PHPUnit\Framework\TestCase;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

class OrderPayloadUtilityTest extends TestCase
{
    /**
     * @dataProvider amountWithBreakdownDiffProvider
     */
    public function testAmountWithBreakdownDiff($array1, $array2, $expected, $message)
    {
        $result = OrderPayloadUtility::amountWithBreakdownDiff($array1, $array2);
        $this->assertEquals($expected, $result, $message);
    }

    public function amountWithBreakdownDiffProvider()
    {
        return [
            'identical amounts with same decimal precision' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [],
                'Identical amounts should have no difference',
            ],
            'different decimal precision but same value' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.0',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '8.5'],
                        'shipping' => ['currency_code' => 'USD', 'value' => '1.5'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '8.50'],
                        'shipping' => ['currency_code' => 'USD', 'value' => '1.50'],
                    ],
                ],
                [],
                'Different decimal precision should be normalized and equal',
            ],
            'missing breakdown property with zero value in payload' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'tax_total' => ['currency_code' => 'USD', 'value' => '0.00'],
                    ],
                ],
                [],
                'Missing breakdown property with 0.00 value should be ignored',
            ],
            'missing breakdown property with zero value (0.0 format)' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'discount' => ['currency_code' => 'USD', 'value' => '0.0'],
                    ],
                ],
                [],
                'Missing breakdown property with 0.0 value should be ignored',
            ],
            'missing breakdown property with non-zero value' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '11.50',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'tax_total' => ['currency_code' => 'USD', 'value' => '1.50'],
                    ],
                ],
                [
                    'value' => '10.00',
                    'breakdown' => [
                        'tax_total' => ['currency_code' => 'USD', 'value' => '1.50'],
                    ],
                ],
                'Missing breakdown property with non-zero value should be detected',
            ],
            'actual value difference' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '15.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '15.00'],
                    ],
                ],
                [
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['value' => '10.00'],
                    ],
                ],
                'Different values should be detected',
            ],
            'currency code difference' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                ],
                [
                    'currency_code' => 'EUR',
                    'value' => '10.00',
                ],
                [
                    'currency_code' => 'USD',
                ],
                'Different currency codes should be detected',
            ],
            'multiple breakdown items with mixed zero and non-zero' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'shipping' => ['currency_code' => 'USD', 'value' => '2.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'shipping' => ['currency_code' => 'USD', 'value' => '2.00'],
                        'tax_total' => ['currency_code' => 'USD', 'value' => '0.00'],
                        'discount' => ['currency_code' => 'USD', 'value' => '0.0'],
                    ],
                ],
                [],
                'Multiple zero-value breakdown items should all be ignored',
            ],
            'breakdown property removed (exists in array1, not in array2)' => [
                [
                    'currency_code' => 'USD',
                    'value' => '12.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'shipping' => ['currency_code' => 'USD', 'value' => '2.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [
                    'value' => '12.00',
                    'breakdown' => [
                        'shipping' => ['currency_code' => 'USD', 'value' => '2.00'],
                    ],
                ],
                'Removed breakdown property with non-zero value should be detected',
            ],
            'jpy amounts identical integer values' => [
                [
                    'currency_code' => 'JPY',
                    'value' => '1500',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1500'],
                    ],
                ],
                [
                    'currency_code' => 'JPY',
                    'value' => '1500',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1500'],
                    ],
                ],
                [],
                'Identical JPY amounts should have no difference',
            ],
            'jpy amounts equal after zero-decimal normalisation' => [
                [
                    'currency_code' => 'JPY',
                    'value' => '1500',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1500'],
                    ],
                ],
                [
                    'currency_code' => 'JPY',
                    'value' => '1500.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1500.00'],
                    ],
                ],
                [],
                'JPY "1500" and "1500.00" should normalise to the same integer and be equal',
            ],
            'jpy amounts that actually differ' => [
                [
                    'currency_code' => 'JPY',
                    'value' => '1500',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1500'],
                    ],
                ],
                [
                    'currency_code' => 'JPY',
                    'value' => '1501',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'JPY', 'value' => '1501'],
                    ],
                ],
                [
                    'value' => '1500',
                    'breakdown' => [
                        'item_total' => ['value' => '1500'],
                    ],
                ],
                'JPY amounts differing by 1 unit should be detected',
            ],
            'breakdown property removed but was zero value' => [
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                        'discount' => ['currency_code' => 'USD', 'value' => '0.00'],
                    ],
                ],
                [
                    'currency_code' => 'USD',
                    'value' => '10.00',
                    'breakdown' => [
                        'item_total' => ['currency_code' => 'USD', 'value' => '10.00'],
                    ],
                ],
                [],
                'Removed breakdown property with zero value should be ignored',
            ],
        ];
    }
}
