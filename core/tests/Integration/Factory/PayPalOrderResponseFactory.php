<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;

class PayPalOrderResponseFactory
{
    public static function create(array $data = []): PayPalOrderResponse
    {
        $defaultData = [
            'id' => 'TEST-ORDER-123',
            'status' => 'COMPLETED',
            'intent' => 'CAPTURE',
            'payer' => '***',
            'payment_source' => [
                'paypal' => [
                    'email_address' => 'test@business.example.com',
                    'account_id' => 'TEST_ACCOUNT_123',
                    'account_status' => 'VERIFIED',
                    'name' => [
                        'given_name' => 'John',
                        'surname' => 'Doe',
                    ],
                    'business_name' => 'Test Store',
                    'address' => [
                        'address_line_1' => '123 Test Street',
                        'address_line_2' => 'Floor 1',
                        'admin_area_2' => 'Paris',
                        'postal_code' => '75002',
                        'country_code' => 'FR',
                    ],
                    'attributes' => [
                        'vault' => [
                            'id' => 'TEST-VAULT-123',
                            'customer' => ['id' => 'TEST-CUSTOMER-123'],
                            'status' => 'ACTIVE',
                        ],
                    ],
                ],
            ],
            'purchase_units' => [
                [
                    'reference_id' => '1',
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => '29.00',
                        'breakdown' => [
                            'item_total' => ['currency_code' => 'EUR', 'value' => '29.00'],
                            'shipping' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'handling' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'tax_total' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'discount' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'insurance' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'shipping_discount' => ['currency_code' => 'EUR', 'value' => '0.00'],
                        ],
                    ],
                    'payee' => [
                        'merchant_id' => 'TEST_MERCHANT_ID',
                        'display_data' => ['brand_name' => 'Test Store'],
                    ],
                    'payment_instruction' => ['disbursement_mode' => 'INSTANT'],
                    'description' => 'Test Order Description',
                    'custom_id' => 'test-uuid-123',
                    'invoice_id' => '',
                    'items' => [
                        [
                            'name' => 'Test Product',
                            'unit_amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                            'tax' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'quantity' => 1,
                            'description' => 'Test Description',
                            'sku' => 'TEST_SKU',
                            'category' => 'PHYSICAL_GOODS',
                        ],
                    ],
                    'shipping' => [
                        'name' => '***',
                        'address' => [
                            'address_line_1' => '***',
                            'address_line_2' => '***',
                            'admin_area_2' => 'Paris',
                            'postal_code' => '75002',
                            'country_code' => 'FR',
                        ],
                    ],
                    'soft_descriptor' => 'TEST STORE',
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'TEST-CAPTURE-123',
                                'status' => 'COMPLETED',
                                'amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                                'final_capture' => true,
                                'disbursement_mode' => 'INSTANT',
                                'seller_protection' => [
                                    'status' => 'ELIGIBLE',
                                    'dispute_categories' => [
                                        'ITEM_NOT_RECEIVED',
                                        'UNAUTHORIZED_TRANSACTION',
                                    ],
                                ],
                                'seller_receivable_breakdown' => [
                                    'gross_amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                                    'paypal_fee' => ['currency_code' => 'EUR', 'value' => '1.38'],
                                    'net_amount' => ['currency_code' => 'EUR', 'value' => '27.62'],
                                ],
                                'custom_id' => 'test-uuid-123',
                                'create_time' => '2025-01-01T10:00:00Z',
                                'update_time' => '2025-01-01T10:00:00Z',
                                'links' => [
                                    [
                                        'href' => 'https://api.test.paypal.com/v2/payments/captures/TEST-CAPTURE-123',
                                        'rel' => 'self',
                                        'method' => 'GET',
                                    ],
                                    [
                                        'href' => 'https://api.test.paypal.com/v2/payments/captures/TEST-CAPTURE-123/refund',
                                        'rel' => 'refund',
                                        'method' => 'POST',
                                    ],
                                    [
                                        'href' => 'https://api.test.paypal.com/v2/checkout/orders/TEST-ORDER-123',
                                        'rel' => 'up',
                                        'method' => 'GET',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'links' => [
                [
                    'href' => 'https://api.test.paypal.com/v2/checkout/orders/TEST-ORDER-123',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
                [
                    'href' => 'https://api.test.paypal.com/v2/checkout/orders/TEST-ORDER-123',
                    'rel' => 'update',
                    'method' => 'PATCH',
                ],
                [
                    'href' => 'https://api.test.paypal.com/v2/checkout/orders/TEST-ORDER-123/capture',
                    'rel' => 'capture',
                    'method' => 'POST',
                ],
            ],
            'create_time' => '2025-01-01T10:00:00Z',
        ];

        $data = array_merge($defaultData, $data);

        return new PayPalOrderResponse(
            $data['id'],
            $data['status'],
            $data['intent'],
            $data['payer'],
            $data['payment_source'],
            $data['purchase_units'],
            $data['links'],
            $data['create_time']
        );
    }
}
