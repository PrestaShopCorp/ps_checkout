<?php

namespace PsCheckout\Core\Tests\Integration\Response;

class CaptureOrderResponse
{
    public static function getSuccessResponse(): array
    {
        return [
            'id' => 'PAY-TEST-123',
            'intent' => 'CAPTURE',
            'status' => 'COMPLETED',
            'payment_source' => [
                'paypal' => [
                    'email_address' => 'test@business.example.com',
                    'account_id' => 'TEST123456',
                    'account_status' => 'VERIFIED',
                    'name' => [
                        'given_name' => 'John',
                        'surname' => 'Doe',
                    ],
                    'business_name' => 'Test Store',
                    'address' => [
                        'address_line_1' => '123 Test Street',
                        'address_line_2' => 'Unit 1',
                        'admin_area_2' => 'Test City',
                        'postal_code' => '12345',
                        'country_code' => 'FR',
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
                            'insurance' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'shipping_discount' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'discount' => ['currency_code' => 'EUR', 'value' => '0.00'],
                        ],
                    ],
                    'payee' => [
                        'merchant_id' => 'MERCHANT123',
                        'display_data' => ['brand_name' => 'Test Store'],
                    ],
                    'payment_instruction' => ['disbursement_mode' => 'INSTANT'],
                    'description' => 'Test order #123',
                    'custom_id' => 'test-uuid-123',
                    'items' => [
                        [
                            'name' => 'Test Product',
                            'unit_amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                            'tax' => ['currency_code' => 'EUR', 'value' => '0.00'],
                            'quantity' => 1,
                            'description' => 'Test Description',
                            'sku' => 'TEST_SKU_1',
                            'category' => 'PHYSICAL_GOODS',
                        ],
                    ],
                    'shipping' => [
                        'name' => 'Test Shipping',
                        'address' => [
                            'address_line_1' => '123 Ship St',
                            'address_line_2' => 'Unit 2',
                            'admin_area_2' => 'Ship City',
                            'postal_code' => '12345',
                            'country_code' => 'FR',
                        ],
                    ],
                    'payments' => [
                        'captures' => [
                            [
                                'id' => 'CAP-TEST-123',
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
                                    'paypal_fee' => ['currency_code' => 'EUR', 'value' => '1.00'],
                                    'net_amount' => ['currency_code' => 'EUR', 'value' => '28.00'],
                                ],
                                'custom_id' => 'test-uuid-123',
                                'create_time' => '2024-01-01T10:00:00Z',
                                'update_time' => '2024-01-01T10:00:00Z',
                            ],
                        ],
                    ],
                ],
            ],
            'create_time' => '2024-01-01T10:00:00Z',
            'update_time' => '2024-01-01T10:00:00Z',
            'links' => [
                [
                    'href' => 'https://api.test.paypal.com/v2/checkout/orders/PAY-TEST-123',
                    'rel' => 'self',
                    'method' => 'GET',
                ],
            ],
        ];
    }
}
