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

namespace PsCheckout\Tests\Api\Unit\ValueObject;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;

class PayPalOrderResponseTest extends TestCase
{
    public function testItReturnsCardAuthenticationResult(): void
    {
        $response = $this->createResponse([
            'card' => [
                'last_digits' => '0004',
                'expiry' => '2030-01',
                'brand' => 'VISA',
                'type' => 'CREDIT',
                'authentication_result' => [
                    'liability_shift' => 'POSSIBLE',
                    'three_d_secure' => [
                        'enrollment_status' => 'Y',
                        'authentication_status' => 'Y',
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            [
                'liability_shift' => 'POSSIBLE',
                'three_d_secure' => [
                    'enrollment_status' => 'Y',
                    'authentication_status' => 'Y',
                ],
            ],
            $response->getAuthenticationResult()
        );
        $this->assertSame('POSSIBLE', $response->getLiabilityShift());
        $this->assertSame(['enrollment_status' => 'Y', 'authentication_status' => 'Y'], $response->get3dSecure());
        $this->assertSame('Y', $response->get3dSecureAuthenticationStatus());
        $this->assertSame('Y', $response->get3dSecureEnrollmentStatus());
    }

    /**
     * @dataProvider provideResponsesWithoutAuthenticationResult
     *
     * @param array<string, array<string, mixed>>|null $paymentSource
     */
    public function testItReturnsNullWhenThereIsNoAuthenticationResult($paymentSource): void
    {
        $response = $this->createResponse($paymentSource);

        $this->assertNull($response->getAuthenticationResult());
        $this->assertNull($response->getLiabilityShift());
        $this->assertNull($response->get3dSecure());
    }

    /**
     * @return array<string, array{0: array<string, array<string, mixed>>|null}>
     */
    public function provideResponsesWithoutAuthenticationResult(): array
    {
        return [
            'no payment source' => [null],
            'card without 3DS' => [['card' => ['last_digits' => '0004', 'brand' => 'VISA']]],
            'paypal wallet' => [['paypal' => ['account_id' => 'PAYERID123']]],
        ];
    }

    /**
     * Shape of a PayPal "GET /v2/checkout/orders/{id}" response.
     *
     * @param array<string, array<string, mixed>>|null $paymentSource
     *
     * @return PayPalOrderResponse
     */
    private function createResponse($paymentSource): PayPalOrderResponse
    {
        return new PayPalOrderResponse(
            '4RA67742YD489830M',
            'CREATED',
            'CAPTURE',
            null,
            $paymentSource,
            [
                [
                    'reference_id' => 'default',
                    'amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                ],
            ],
            [
                ['href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4RA67742YD489830M', 'rel' => 'self', 'method' => 'GET'],
                ['href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4RA67742YD489830M/capture', 'rel' => 'capture', 'method' => 'POST'],
            ]
        );
    }
}
