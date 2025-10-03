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

namespace Tests\PsCheckout\Core\Order\Builder\Node;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\BaseNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Utility\Common\NumberUtility;
use PsCheckout\Utility\Common\StringUtility;

class BaseNodeBuilderTest extends TestCase
{
    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(array $configurationData, array $cartData, bool $isVault, bool $isUpdate, $paypalOrderId, array $expected)
    {
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $configurationMock->method('get')
            ->willReturnCallback(function ($key) use ($configurationData) {
                return $configurationData[$key] ?? null;
            });

        $builder = new BaseNodeBuilder($configurationMock);
        $builder->setCart($cartData)
            ->setIsVault($isVault)
            ->setIsUpdate($isUpdate)
            ->setPaypalOrderId($paypalOrderId);

        $result = $builder->build();
        $this->assertEquals($expected, $result);
    }

    public function buildDataProvider(): array
    {
        return [
            'new_order_without_vault' => [
                'configurationData' => [
                    'PS_SHOP_NAME' => 'Test Shop',
                    PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT => 'MERCHANT123',
                    PayPalConfiguration::PS_CHECKOUT_INTENT => 'CAPTURE',
                    PayPalConfiguration::PS_ROUND_TYPE => 'round',
                    PayPalConfiguration::PS_PRICE_ROUND_MODE => 'up',
                ],
                'cartData' => [
                    'cart' => [
                        'id' => 123,
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 100.50,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'USD',
                    ],
                ],
                'isVault' => false,
                'isUpdate' => false,
                'paypalOrderId' => null,
                'expected' => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'custom_id' => '123',
                            'invoice_id' => '',
                            'description' => StringUtility::truncate('Checking out with your cart #123 from Test Shop', 127),
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => NumberUtility::formatAmount(100.50, 'USD'),
                            ],
                            'payee' => [
                                'merchant_id' => 'MERCHANT123',
                            ],
                        ],
                    ],
                ],
            ],
            'update_order_with_vault' => [
                'configurationData' => [
                    'PS_SHOP_NAME' => 'Test Shop',
                    PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT => 'MERCHANT123',
                    PayPalConfiguration::PS_CHECKOUT_INTENT => 'AUTHORIZE',
                ],
                'cartData' => [
                    'cart' => [
                        'id' => 456,
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 200.75,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'EUR',
                    ],
                ],
                'isVault' => true,
                'isUpdate' => true,
                'paypalOrderId' => 'PAYPAL123',
                'expected' => [
                    'intent' => 'AUTHORIZE',
                    'purchase_units' => [
                        [
                            'custom_id' => '456',
                            'invoice_id' => '',
                            'description' => StringUtility::truncate('Checking out with your cart #456 from Test Shop', 127),
                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => NumberUtility::formatAmount(200.75, 'EUR'),
                            ],
                            'payee' => [
                                'merchant_id' => 'MERCHANT123',
                            ],
                        ],
                    ],
                    'id' => 'PAYPAL123',
                ],
            ],
            'new_order_with_vault' => [
                'configurationData' => [
                    'PS_SHOP_NAME' => 'Another Shop',
                    PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT => 'MERCHANT456',
                    PayPalConfiguration::PS_CHECKOUT_INTENT => 'CAPTURE',
                    PayPalConfiguration::PS_ROUND_TYPE => 'round',
                    PayPalConfiguration::PS_PRICE_ROUND_MODE => 'down',
                ],
                'cartData' => [
                    'cart' => [
                        'id' => 789,
                        'totals' => [
                            'total_including_tax' => [
                                'amount' => 150.25,
                            ],
                        ],
                    ],
                    'currency' => [
                        'iso_code' => 'GBP',
                    ],
                ],
                'isVault' => true,
                'isUpdate' => false,
                'paypalOrderId' => null,
                'expected' => [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'custom_id' => '789',
                            'invoice_id' => '',
                            'description' => StringUtility::truncate('Checking out with your cart #789 from Another Shop', 127),
                            'amount' => [
                                'currency_code' => 'GBP',
                                'value' => NumberUtility::formatAmount(150.25, 'GBP'),
                            ],
                            'payee' => [
                                'merchant_id' => 'MERCHANT456',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
