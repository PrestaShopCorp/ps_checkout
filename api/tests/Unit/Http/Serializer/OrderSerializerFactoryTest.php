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
use PsCheckout\Api\Dto\PayPal\Address;
use PsCheckout\Api\Dto\PayPal\AmountBreakdown;
use PsCheckout\Api\Dto\PayPal\AmountWithBreakdown;
use PsCheckout\Api\Dto\PayPal\Item;
use PsCheckout\Api\Dto\PayPal\ItemCategory;
use PsCheckout\Api\Dto\PayPal\ItemRequest;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\Order\CreateOrderRequestDto;
use PsCheckout\Api\Dto\PayPal\Order\CreateOrderResponseDto;
use PsCheckout\Api\Dto\PayPal\OrderIntent;
use PsCheckout\Api\Dto\PayPal\PayeeBase;
use PsCheckout\Api\Dto\PayPal\PayeePaymentMethodPreference;
use PsCheckout\Api\Dto\PayPal\PaymentInitiator;
use PsCheckout\Api\Dto\PayPal\PaymentSource;
use PsCheckout\Api\Dto\PayPal\PaypalExperienceLandingPage;
use PsCheckout\Api\Dto\PayPal\PaypalExperienceUserAction;
use PsCheckout\Api\Dto\PayPal\PaypalWallet;
use PsCheckout\Api\Dto\PayPal\PaypalWalletContextShippingPreference;
use PsCheckout\Api\Dto\PayPal\PaypalWalletExperienceContext;
use PsCheckout\Api\Dto\PayPal\PaypalWalletStoredCredential;
use PsCheckout\Api\Dto\PayPal\PurchaseUnitRequest;
use PsCheckout\Api\Dto\PayPal\ShippingDetails;
use PsCheckout\Api\Dto\PayPal\ShippingType;
use PsCheckout\Api\Dto\PayPal\StoredPaymentSourceUsageType;
use PsCheckout\Api\Dto\PayPal\UniversalProductCode;
use PsCheckout\Api\Dto\PayPal\UsagePattern;
use PsCheckout\Api\Http\Serializer\OrderSerializerFactory;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * @coversDefaultClass OrderSerializerFactory
 */
class OrderSerializerFactoryTest extends TestCase
{
    /**
     * @dataProvider requestPayloadDataProvider
     *
     * @param mixed $object
     * @param array<string, mixed> $payload
     */
    public function testRequestSerializer($object, array $payload): void
    {
        $serializer = OrderSerializerFactory::create();

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
            'Sample 1 - 200 - Create Order - PayPal Wallet as Payment Source, Resulting in PAYER_ACTION_REQUIRED Response' => [
                'object' => (new CreateOrderRequestDto(
                    OrderIntent::CAPTURE,
                    [
                        (new PurchaseUnitRequest(
                            (new AmountWithBreakdown('USD', '230.00'))->setBreakdown(
                                (new AmountBreakdown())
                                    ->setItemTotal(new Money('USD', '220.00'))
                                    ->setShipping(new Money('USD', '10.00'))
                            )
                        ))->setItems([
                            (new ItemRequest('T-Shirt', new Money('USD', '20.00'), '1'))
                                ->setDescription('Super Fresh Shirt')
                                ->setCategory(ItemCategory::PHYSICAL_GOODS)
                                ->setSku('sku01')
                                ->setImageUrl('https://example.com/static/images/items/1/tshirt_green.jpg')
                                ->setUrl('https://example.com/url-to-the-item-being-purchased-1')
                                ->setUpc(new UniversalProductCode('UPC-A', '123456789012')),
                            (new ItemRequest('Shoes', new Money('USD', '100.00'), '2'))
                                ->setDescription('Running, Size 10.5')
                                ->setCategory(ItemCategory::PHYSICAL_GOODS)
                                ->setSku('sku02')
                                ->setImageUrl('https://example.com/static/images/items/1/shoes_running.jpg')
                                ->setUrl('https://example.com/url-to-the-item-being-purchased-2')
                                ->setUpc(new UniversalProductCode('UPC-A', '987654321012'))
                        ])
                    ]
                ))->setPaymentSource((new PaymentSource())->setPaypal((new PaypalWallet())->setExperienceContext(
                    (new PaypalWalletExperienceContext())
                        ->setPaymentMethodPreference(PayeePaymentMethodPreference::IMMEDIATE_PAYMENT_REQUIRED)
                        ->setLandingPage(PaypalExperienceLandingPage::LOGIN)
                        ->setShippingPreference(PaypalWalletContextShippingPreference::GET_FROM_FILE)
                        ->setUserAction(PaypalExperienceUserAction::PAY_NOW)
                        ->setReturnUrl('https://example.com/returnUrl')
                        ->setCancelUrl('https://example.com/cancelUrl')
                ))),
                'payload' => [],
            ],
            'Sample 2 - 201 - Create and authorize an order for a PayPal wallet vaulted account holder passing a usage pattern indicator using stored credentials (Single Shot).' => [
                'object' => (new CreateOrderRequestDto(
                    OrderIntent::AUTHORIZE,
                    [new PurchaseUnitRequest(
                        new AmountWithBreakdown('USD', '100.00')
                    )]
                ))->setPaymentSource(
                    (new PaymentSource())->setPaypal(
                        (new PaypalWallet())
                            ->setVaultId('2w915838hr181240m')
                            ->setStoredCredential(
                                (new PaypalWalletStoredCredential(
                                    PaymentInitiator::CUSTOMER
                                ))->setUsage(StoredPaymentSourceUsageType::SUBSEQUENT)
                                    ->setUsagePattern(UsagePattern::RECURRING_PREPAID)
                            )
                    )
                )->setPurchaseUnits([new PurchaseUnitRequest(
                    new AmountWithBreakdown('USD', '100.00')
                )]),
                'payload' => [],
            ],
            'Sample 3 - 201 - Create Order - Minimal Request and Response' => [
                'object' => new CreateOrderRequestDto(
                    OrderIntent::CAPTURE,
                    [new PurchaseUnitRequest(
                        new AmountWithBreakdown('USD', '100.00')
                    )]
                ),
                'payload' => [],
            ],
            'Sample 4 - 201 - Create Order - Buy Online Pickup In Store Shipping Type' => [
                'object' => new CreateOrderRequestDto(
                    OrderIntent::AUTHORIZE,
                    [(new PurchaseUnitRequest(new AmountWithBreakdown('USD', '50.00')))
                        ->setReferenceId('PUHF')
                        ->setPayee(new PayeeBase('merchant@example.com'))
                        ->setShipping(
                            (
                                (new ShippingDetails())
                            ->setType(ShippingType::PICKUP_IN_STORE)
                            ->setAddress(
                                new Address(
                                    'US',
                                    '123 Townsend St',
                                    'Floor 6',
                                    'San Francisco',
                                    'CA',
                                    '94107'
                                )
                            )
                            )
                        )]
                ),
                'payload' => [],
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
        $serializer = OrderSerializerFactory::create();
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
            'Sample 1 - 200 - Create Order - PayPal Wallet as Payment Source, Resulting in PAYER_ACTION_REQUIRED Response' => [
                'class' => CreateOrderResponseDto::class,
                'payload' => [
                    'id' => '5O190127TN364715T',
                    'status' => 'PAYER_ACTION_REQUIRED',
                    'payment_source' => [
                        'paypal' => []
                    ],
                    'links' => [
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/5O190127TN364715T',
                            'rel' => 'self',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://www.paypal.com/checkoutnow?token=5O190127TN364715T',
                            'rel' => 'payer-action',
                            'method' => 'GET'
                        ]
                    ]
                ],
            ],
            'Sample 2 - 201 - Create and authorize an order for a PayPal wallet vaulted account holder passing a usage pattern indicator using stored credentials (Single Shot).' => [
                'class' => CreateOrderResponseDto::class,
                'payload' => [
                    'id' => '8K832279M55989614',
                    'intent' => 'AUTHORIZE',
                    'status' => 'COMPLETED',
                    'payment_source' => [
                        'paypal' => [
                            'email_address' => 'buyer@paypal.com',
                            'account_id' => 'XQHSN372NGSP2',
                            'account_status' => 'UNVERIFIED',
                            'name' => [
                                'given_name' => 'Tom',
                                'surname' => 'Business'
                            ],
                            'phone_number' => [
                                'national_number' => '8127081430'
                            ],
                            'address' => [
                                'country_code' => 'US'
                            ],
                            'stored_credential' => [
                                'payment_initiator' => 'MERCHANT',
                                'usage' => 'SUBSEQUENT',
                                'usage_pattern' => 'RECURRING_PREPAID'
                            ]
                        ]
                    ],
                    'purchase_units' => [
                        [
                            'reference_id' => 'default',
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => '100.00'
                            ],
                            'payee' => [
                                'email_address' => 'seller@paypal.com',
                                'merchant_id' => 'Q7C9N6S4YRQXG'
                            ],
                            'shipping' => [
                                'name' => [
                                    'full_name' => 'Tom Business'
                                ],
                                'address' => [
                                    'address_line_1' => '123 Fake St.',
                                    'admin_area_2' => 'Baton Rouge',
                                    'admin_area_1' => 'LA',
                                    'postal_code' => '70802',
                                    'country_code' => 'US'
                                ]
                            ],
                            'payments' => [
                                'authorizations' => [
                                    [
                                        'status' => 'CREATED',
                                        'id' => '7X550106NA4440028',
                                        'amount' => [
                                            'currency_code' => 'USD',
                                            'value' => '100.00'
                                        ],
                                        'seller_protection' => [
                                            'status' => 'ELIGIBLE',
                                            'dispute_categories' => [
                                                'UNAUTHORIZED_TRANSACTION'
                                            ]
                                        ],
                                        'expiration_time' => '2024-06-05T18:46:54Z',
                                        'links' => [
                                            [
                                                'href' => 'https://api-m.paypal.com/v2/payments/authorizations/7X550106NA4440028',
                                                'rel' => 'self',
                                                'method' => 'GET'
                                            ],
                                            [
                                                'href' => 'https://api-m.paypal.com/v2/payments/authorizations/7X550106NA4440028/capture',
                                                'rel' => 'capture',
                                                'method' => 'POST'
                                            ],
                                            [
                                                'href' => 'https://api-m.paypal.com/v2/payments/authorizations/7X550106NA4440028/void',
                                                'rel' => 'void',
                                                'method' => 'POST'
                                            ],
                                            [
                                                'href' => 'https://api-m.paypal.com/v2/checkout/orders/8K832279M55989614',
                                                'rel' => 'up',
                                                'method' => 'GET'
                                            ]
                                        ],
                                        'create_time' => '2024-05-07T18:46:54Z',
                                        'update_time' => '2024-05-07T18:46:54Z'
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'payer' => [
                        'name' => [
                            'given_name' => 'Tom',
                            'surname' => 'Business'
                        ],
                        'email_address' => 'buyer@paypal.com',
                        'payer_id' => 'XQHSN372NGSP2',
                        'phone' => [
                            'phone_number' => [
                                'national_number' => '8127081430'
                            ]
                        ],
                        'address' => [
                            'country_code' => 'US'
                        ]
                    ],
                    'create_time' => '2024-05-07T18:46:15Z',
                    'update_time' => '2024-05-07T18:46:54Z',
                    'links' => [
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/8K832279M55989614',
                            'rel' => 'self',
                            'method' => 'GET'
                        ]
                    ]
                ],
            ],
            'Sample 3 - 201 - Create Order - Minimal Request and Response' => [
                'class' => CreateOrderResponseDto::class,
                'payload' => [
                    'id' => '5O190127TN364715T',
                    'status' => 'CREATED',
                    'links' => [
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/5O190127TN364715T',
                            'rel' => 'self',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://www.paypal.com/checkoutnow?token=5O190127TN364715T',
                            'rel' => 'approve',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/5O190127TN364715T',
                            'rel' => 'update',
                            'method' => 'PATCH'
                        ],
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/5O190127TN364715T/capture',
                            'rel' => 'capture',
                            'method' => 'POST'
                        ]
                    ]
                ],
            ],
            'Sample 4 - 201 - Create Order - Buy Online Pickup In Store Shipping Type' => [
                'class' => CreateOrderResponseDto::class,
                'payload' => [
                    'id' => '4SP74185RW2405200',
                    'intent' => 'AUTHORIZE',
                    'purchase_units' => [
                        [
                            'reference_id' => 'PUHF',
                            'amount' => [
                                'currency_code' => 'USD',
                                'value' => '50.00'
                            ],
                            'payee' => [
                                'email_address' => 'merchant@example.com'
                            ],
                            'shipping' => [
                                'address' => [
                                    'address_line_1' => '123 Townsend St',
                                    'address_line_2' => 'Floor 6',
                                    'admin_area_2' => 'San Francisco',
                                    'admin_area_1' => 'CA',
                                    'postal_code' => '94107',
                                    'country_code' => 'US'
                                ],
                                'type' => 'PICKUP_IN_STORE'
                            ]
                        ]
                    ],
                    'links' => [
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/4SP74185RW2405200',
                            'rel' => 'self',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://www.paypal.com/checkoutnow?token=4SP74185RW2405200',
                            'rel' => 'approve',
                            'method' => 'GET'
                        ],
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/4SP74185RW2405200',
                            'rel' => 'update',
                            'method' => 'PATCH'
                        ],
                        [
                            'href' => 'https://api-m.paypal.com/v2/checkout/orders/4SP74185RW2405200/authorize',
                            'rel' => 'authorize',
                            'method' => 'POST'
                        ]
                    ],
                    'status' => 'CREATED'
                ],
            ],
        ];
    }
}
