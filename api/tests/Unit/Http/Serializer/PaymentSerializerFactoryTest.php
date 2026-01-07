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

namespace PsCheckout\Tests\Api\Unit\Http\Serializer;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\Payment\PaymentAuthorizationResponseDto;
use PsCheckout\Api\Dto\PayPal\Payment\ReauthorizeAuthorizationRequestDto;
use PsCheckout\Api\Http\Serializer\PaymentSerializerFactory;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * @coversDefaultClass PaymentSerializerFactory
 */
class PaymentSerializerFactoryTest extends TestCase
{
    /**
     * @dataProvider requestPayloadDataProvider
     *
     * @param mixed $object
     * @param array<string, mixed> $payload
     */
    public function testRequestSerializer($object, array $payload): void
    {
        $serializer = PaymentSerializerFactory::create();

        $data = $serializer->serialize($object, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ]);

        $this->assertEquals(json_encode($payload), $data);
    }

    /**
     * @return array<string, array{object: mixed, payload: array<string, mixed>}>
     */
    public function requestPayloadDataProvider(): array
    {
        return [
            'Sample 1 - 201 - Reauthorize Authorized Payment with an empty request' => [
                'object' => new ReauthorizeAuthorizationRequestDto(),
                'payload' => [],
            ],
            'Sample 3 - 201 - Reauthorize Authorized Payment' => [
                'object' => new ReauthorizeAuthorizationRequestDto(new Money('USD', '10.99')),
                'payload' => [
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '10.99',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider responsePayloadDataProvider
     *
     * @param class-string $class
     * @param array<string, mixed> $payload
     */
    public function testResponseSerializer(string $class, array $payload): void
    {
        $serializer = PaymentSerializerFactory::create();
        $encodedPayload = json_encode($payload);

        $data = $serializer->deserialize($encodedPayload, $class, JsonEncoder::FORMAT);

        $this->assertInstanceOf($class, $data);
        $this->assertEquals($payload, json_decode($serializer->serialize($data, JsonEncoder::FORMAT, [
            AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
        ]), true));
    }

    /**
     * @return array<string, array{class: class-string, payload: array<string, mixed>}>
     */
    public function responsePayloadDataProvider(): array
    {
        return [
            'Sample 1 - 200 - Show Authorized Payment Details' => [
                'class' => PaymentAuthorizationResponseDto::class,
                'payload' => [
                    'id' => '0VF52814937998046',
                    'status' => 'CREATED',
                    'amount' => [
                        'value' => '10.99',
                        'currency_code' => 'USD'
                    ],
                    'invoice_id' => 'INVOICE-123',
                    'seller_protection' => [
                        'status' => 'ELIGIBLE',
                        'dispute_categories' => [
                            'ITEM_NOT_RECEIVED',
                            'UNAUTHORIZED_TRANSACTION'
                        ]
                    ],
                    'payee' => [
                        'email_address' => 'merchant@example.com',
                        'merchant_id' => '7KNGBPH2U58GQ'
                    ],
                    'expiration_time' => '2017-10-10T23:23:45Z',
                    'create_time' => '2017-09-11T23:23:45Z',
                    'update_time' => '2017-09-11T23:23:45Z',
                    'links' => [
                        [
                            'rel' => 'self',
                            'method' => 'GET',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046'
                        ],
                        [
                            'rel' => 'capture',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/capture'
                        ],
                        [
                            'rel' => 'void',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/void'
                        ],
                        [
                            'rel' => 'reauthorize',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/reauthorize'
                        ]
                    ]
                ],
            ],
            'Sample 2 - 200 - Show Authorized Payment Details' => [
                'class' => PaymentAuthorizationResponseDto::class,
                'payload' => [
                    'id' => '0T620041CK889853A',
                    'status' => 'CREATED',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => '100.00'
                    ],
                    'invoice_id' => 'OrderInvoice-23_10_2024_12_27_32_pm',
                    'seller_protection' => [
                        'status' => 'ELIGIBLE',
                        'dispute_categories' => [
                            'ITEM_NOT_RECEIVED',
                            'UNAUTHORIZED_TRANSACTION'
                        ]
                    ],
                    'supplementary_data' => [
                        'related_ids' => [
                            'order_id' => '25M43554V9523650M'
                        ]
                    ],
                    'payee' => [
                        'email_address' => 'merchant@example.com',
                        'merchant_id' => 'YXZY75W2GKDQE'
                    ],
                    'expiration_time' => '2024-11-21T17:27:36Z',
                    'create_time' => '2024-10-23T17:27:36Z',
                    'update_time' => '2024-10-23T17:27:36Z',
                    'links' => [
                        [
                            'href' => 'https://api-m.sandbox.paypal.com/v2/payments/authorizations/0T620041CK889853A',
                            'rel' => 'self',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://api-m.sandbox.paypal.com/v2/payments/authorizations/0T620041CK889853A/capture',
                            'rel' => 'capture',
                            'method' => 'POST'
                        ],
                        [
                            'href' => 'https://api-m.sandbox.paypal.com/v2/payments/authorizations/0T620041CK889853A/void',
                            'rel' => 'void',
                            'method' => 'POST'
                        ],
                        [
                            'href' => 'https://api-m.sandbox.paypal.com/v2/payments/authorizations/0T620041CK889853A/reauthorize',
                            'rel' => 'reauthorize',
                            'method' => 'POST'
                        ],
                        [
                            'href' => 'https://api-m.sandbox.paypal.com/v2/checkout/orders/25M43554V9523650M',
                            'rel' => 'up',
                            'method' => 'GET'
                        ]
                    ]
                ],
            ],
            'Sample 3 - 201 - Reauthorize Authorized Payment' => [
                'class' => PaymentAuthorizationResponseDto::class,
                'payload' => [
                    'id' => '8AA831015G517922L',
                    'status' => 'CREATED',
                    'links' => [
                        [
                            'rel' => 'self',
                            'method' => 'GET',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L'
                        ],
                        [
                            'rel' => 'capture',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/capture'
                        ],
                        [
                            'rel' => 'void',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/void'
                        ],
                        [
                            'rel' => 'reauthorize',
                            'method' => 'POST',
                            'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/reauthorize'
                        ]
                    ]
                ],
            ],
        ];
    }
}
