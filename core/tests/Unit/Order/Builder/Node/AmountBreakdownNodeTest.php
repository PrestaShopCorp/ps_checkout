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

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\AmountBreakdownNode;
use PsCheckout\Utility\Common\StringUtility;

class AmountBreakdownNodeTest extends TestCase
{
    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $cartData, array $expected)
    {
        $nodeBuilder = new AmountBreakdownNode();
        $nodeBuilder->setCart($cartData);

        $result = $nodeBuilder->build();
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function buildDataProvider(): array
    {
        return [
            'basic_cart_with_tax_and_shipping' => [
                'cartData' => [
                    'cart' => [
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 150.50,
                            ],
                        ],
                        'shipping_cost' => 10.00,
                        'subtotals' => [
                            'gift_wrapping' => [
                                'amount' => 5.00,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'USD',
                    ],
                    'products' => [
                        [
                            'id_product' => 1,
                            'id_product_attribute' => 5,
                            'name' => 'Product 1',
                            'total' => 50.00,
                            'total_wt' => 60.00,
                            'quantity' => 2,
                            'reference' => 'REF123',
                            'is_virtual' => '0',
                            'attributes' => 'Color: Red',
                        ],
                        [
                            'id_product' => 2,
                            'id_product_attribute' => null,
                            'name' => 'Product 2',
                            'total' => 30.00,
                            'total_wt' => 35.00,
                            'quantity' => 1,
                            'ean13' => 'EAN123',
                            'is_virtual' => '1',
                            'attributes' => '',
                        ],
                    ],
                ],
                'expected' => [
                    'items' => [
                        [
                            'name' => StringUtility::truncate('Product 1', 127),
                            'description' => StringUtility::truncate('Color: Red', 127),
                            'sku' => StringUtility::truncate('REF123', 127),
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => '25.00', // 50.00 / 2
                            ],
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '5.00', // (60.00 - 50.00) / 2
                            ],
                            'quantity' => 2,
                            'category' => 'PHYSICAL_GOODS',
                        ],
                        [
                            'name' => StringUtility::truncate('Product 2', 127),
                            'description' => '',
                            'sku' => StringUtility::truncate('2-0', 127),
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => '30.00', // 30.00 / 1
                            ],
                            'tax' => [
                                'currency_code' => 'USD',
                                'value' => '5.00', // (35.00 - 30.00) / 1
                            ],
                            'quantity' => 1,
                            'category' => 'DIGITAL_GOODS',
                        ],
                    ],
                    'amount' => [
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'USD',
                                'value' => '80.00', // (25 * 2) + (30 * 1)
                            ],
                            'shipping' => [
                                'currency_code' => 'USD',
                                'value' => '10.00',
                            ],
                            'tax_total' => [
                                'currency_code' => 'USD',
                                'value' => '15.00', // (5 * 2) + (5 * 1)
                            ],
                            'discount' => [
                                'currency_code' => 'USD',
                                'value' => '0.00',
                            ],
                            'handling' => [
                                'currency_code' => 'USD',
                                'value' => '45.50', // 5.00 (gift_wrapping) + 40.50 (remainder)
                            ],
                        ],
                    ],
                ],
            ],
            'cart_with_jpy_currency' => [
                'cartData' => [
                    'cart' => [
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 15000,
                            ],
                        ],
                        'shipping_cost' => 1000,
                        'subtotals' => [
                            'gift_wrapping' => [
                                'amount' => 500,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'JPY',
                    ],
                    'products' => [
                        [
                            'name' => 'Product A',
                            'total' => 5000,
                            'total_wt' => 6000,
                            'quantity' => 2,
                            'reference' => 'REF456',
                            'is_virtual' => '0',
                            'attributes' => '',
                        ],
                    ],
                ],
                'expected' => [
                    'items' => [
                        [
                            'name' => StringUtility::truncate('Product A', 127),
                            'description' => '',
                            'sku' => StringUtility::truncate('REF456', 127),
                            'unit_amount' => [
                                'currency_code' => 'JPY',
                                'value' => '2500', // 5000 / 2
                            ],
                            'tax' => [
                                'currency_code' => 'JPY',
                                'value' => '500', // (6000 - 5000) / 2
                            ],
                            'quantity' => 2,
                            'category' => 'PHYSICAL_GOODS',
                        ],
                    ],
                    'amount' => [
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'JPY',
                                'value' => '5000', // 2500 * 2
                            ],
                            'shipping' => [
                                'currency_code' => 'JPY',
                                'value' => '1000',
                            ],
                            'tax_total' => [
                                'currency_code' => 'JPY',
                                'value' => '1000', // 500 * 2
                            ],
                            'discount' => [
                                'currency_code' => 'JPY',
                                'value' => '0',
                            ],
                            'handling' => [
                                'currency_code' => 'JPY',
                                'value' => '8000', // 500 (gift_wrapping) + 7500 (remainder)
                            ],
                        ],
                    ],
                ],
            ],
            'cart_with_rounding_issue' => [
                'cartData' => [
                    'cart' => [
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 100.00,
                            ],
                        ],
                        'shipping_cost' => 10.00,
                        'subtotals' => [
                            'gift_wrapping' => [
                                'amount' => 0.00,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'EUR',
                    ],
                    'products' => [
                        [
                            'name' => 'Product A',
                            'total' => 45.00,
                            'total_wt' => 50.00,
                            'quantity' => 1,
                            'reference' => 'REF456',
                            'is_virtual' => '0',
                            'attributes' => '',
                        ],
                    ],
                ],
                'expected' => [
                    'items' => [
                        [
                            'name' => StringUtility::truncate('Product A', 127),
                            'description' => '',
                            'sku' => StringUtility::truncate('REF456', 127),
                            'unit_amount' => [
                                'currency_code' => 'EUR',
                                'value' => '45.00',
                            ],
                            'tax' => [
                                'currency_code' => 'EUR',
                                'value' => '5.00',
                            ],
                            'quantity' => 1,
                            'category' => 'PHYSICAL_GOODS',
                        ],
                    ],
                    'amount' => [
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value' => '45.00',
                            ],
                            'shipping' => [
                                'currency_code' => 'EUR',
                                'value' => '10.00',
                            ],
                            'tax_total' => [
                                'currency_code' => 'EUR',
                                'value' => '5.00',
                            ],
                            'discount' => [
                                'currency_code' => 'EUR',
                                'value' => '0.00',
                            ],
                            'handling' => [
                                'currency_code' => 'EUR',
                                'value' => '40.00', // remainder (100 - 45 - 5 - 10)
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
