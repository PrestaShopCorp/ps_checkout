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

namespace Tests\Unit\PsCheckout\Core\Order\Builder\Node;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\PayPalPaymentSourceNodeBuilder;

class PayPalPaymentSourceNodeBuilderTest extends TestCase
{
    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild($vaultId, $customerId, $savePaymentMethod, $expected): void
    {
        $builder = new PayPalPaymentSourceNodeBuilder();

        if ($vaultId) {
            $builder->setPaypalVaultId($vaultId);
        }
        if ($customerId) {
            $builder->setPaypalCustomerId($customerId);
        }
        if (!is_null($savePaymentMethod)) {
            $builder->setSavePaymentMethod($savePaymentMethod);
        }

        $this->assertEquals($expected, $builder->build());
    }

    public function buildDataProvider(): array
    {
        return [
            'vault ID provided' => [
                'vault_123', null, null, [],
            ],
            'customer ID provided' => [
                null, 'customer_123', null, [
                    'payment_source' => [
                        'paypal' => [
                            'attributes' => [
                                'customer' => ['id' => 'customer_123'],
                            ],
                        ],
                    ],
                ],
            ],
            'save payment method' => [
                null, null, true, [
                    'payment_source' => [
                        'paypal' => [
                            'attributes' => [
                                'vault' => [
                                    'store_in_vault' => 'ON_SUCCESS',
                                    'usage_pattern' => 'IMMEDIATE',
                                    'usage_type' => 'MERCHANT',
                                    'customer_type' => 'CONSUMER',
                                    'permit_multiple_payment_tokens' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'customer ID and save payment method' => [
                null, 'customer_123', true, [
                    'payment_source' => [
                        'paypal' => [
                            'attributes' => [
                                'customer' => ['id' => 'customer_123'],
                                'vault' => [
                                    'store_in_vault' => 'ON_SUCCESS',
                                    'usage_pattern' => 'IMMEDIATE',
                                    'usage_type' => 'MERCHANT',
                                    'customer_type' => 'CONSUMER',
                                    'permit_multiple_payment_tokens' => false,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'no data provided' => [
                null, null, null, [],
            ],
            'vault ID and customer ID' => [
                'vault_123', 'customer_123', true, [],
            ],
        ];
    }
}
