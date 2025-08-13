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
use PsCheckout\Core\Order\Builder\Node\GooglePayPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;

class GooglePayPaymentSourceNodeBuilderTest extends TestCase
{
    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild($is3DSecureEnabled, $contingency, $expected): void
    {
        $payPalConfigMock = $this->createMock(PayPalConfiguration::class);
        $payPalConfigMock->method('is3dSecureEnabled')->willReturn($is3DSecureEnabled);

        // Ensure getCardFieldsContingencies() always returns a string
        $payPalConfigMock->method('getCardFieldsContingencies')->willReturn($contingency ?? '');

        $builder = new GooglePayPaymentSourceNodeBuilder($payPalConfigMock);

        $this->assertEquals($expected, $builder->build());
    }

    public function buildDataProvider(): array
    {
        return [
            '3D Secure enabled with SCA_ALWAYS' => [
                true, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'google_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => 'SCA_ALWAYS',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '3D Secure enabled with CVV_ONLY' => [
                true, 'CVV_ONLY', [
                    'payment_source' => [
                        'google_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => 'CVV_ONLY',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '3D Secure disabled' => [
                false, 'SCA_ALWAYS', [],
            ],
            '3D Secure enabled with empty string contingency' => [
                true, '', [
                    'payment_source' => [
                        'google_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '3D Secure enabled with null contingency (converted to empty string)' => [
                true, null, [  // null will be converted to ''
                    'payment_source' => [
                        'google_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => '',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
