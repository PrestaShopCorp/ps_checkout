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

namespace Tests\Unit\PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\ApplePayPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\LinkInterface;

class ApplePayPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(bool $is3dSecureEnabled = false, string $contingency = 'SCA_ALWAYS'): ApplePayPaymentSourceNodeBuilder
    {
        $payPalConfig = $this->createMock(PayPalConfiguration::class);
        $payPalConfig->method('is3dSecureEnabled')->willReturn($is3dSecureEnabled);
        $payPalConfig->method('getCardFieldsContingencies')->willReturn($contingency);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        return new ApplePayPaymentSourceNodeBuilder($payPalConfig, $link);
    }

    private function makeContext(): CheckoutContext
    {
        return new CheckoutContext([], 'apple_pay', false, null, null, false, false);
    }

    public function testSupportsApplePay(): void
    {
        $builder = $this->makeBuilder();
        $this->assertTrue($builder->supports('apple_pay'));
        $this->assertFalse($builder->supports('paypal'));
    }

    public function testAlwaysReturnsExperienceContext(): void
    {
        $result = $this->makeBuilder(false)->build($this->makeContext());

        $this->assertSame([
            'payment_source' => [
                'apple_pay' => [
                    'experience_context' => [
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    /**
     * @dataProvider buildDataProvider
     * @param array<string, mixed> $expected
     */
    public function testBuild(bool $is3dSecureEnabled, string $contingency, array $expected): void
    {
        $this->assertSame($expected, $this->makeBuilder($is3dSecureEnabled, $contingency)->build($this->makeContext()));
    }

    /**
     * @return array<string, array{bool, string, array<string, mixed>}>
     */
    public static function buildDataProvider(): array
    {
        $experienceContext = [
            'return_url' => 'https://example.com/validate',
            'cancel_url' => 'https://example.com/cancel',
        ];

        return [
            '3DS disabled returns only experience_context' => [
                false, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'apple_pay' => [
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            '3DS enabled with SCA_ALWAYS' => [
                true, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'apple_pay' => [
                            'experience_context' => $experienceContext,
                            'attributes' => [
                                'verification' => [
                                    'method' => 'SCA_ALWAYS',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '3DS enabled with SCA_WHEN_REQUIRED' => [
                true, 'SCA_WHEN_REQUIRED', [
                    'payment_source' => [
                        'apple_pay' => [
                            'experience_context' => $experienceContext,
                            'attributes' => [
                                'verification' => [
                                    'method' => 'SCA_WHEN_REQUIRED',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
