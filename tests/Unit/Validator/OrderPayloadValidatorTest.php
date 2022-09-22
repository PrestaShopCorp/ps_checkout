<?php

namespace Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\OrderValidationException;
use PrestaShop\Module\PrestashopCheckout\Validator\OrderPayloadValidator;

class OrderPayloadValidatorTest extends TestCase
{
    /**
     * @var OrderPayloadValidator
     */
    private $orderPayloadValidator;

    protected function setUp()
    {
        $this->orderPayloadValidator = new OrderPayloadValidator();
    }

    public function testOrderPayloadValidatorIntentException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_INVALID_INTENT);
        $this->orderPayloadValidator->checkBaseNode($this->nodeProvider('FAILURE'));
    }

    public function testOrderPayloadValidatorCurrencyCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_CURRENCY_CODE_INVALID);
        $this->orderPayloadValidator->checkBaseNode($this->nodeProvider('XXX'));
    }

    public function testOrderPayloadValidatorAmountException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_AMOUNT_EMPTY);
        $this->orderPayloadValidator->checkBaseNode($this->nodeProvider('-1'));
    }

    public function testOrderPayloadValidatorMerchantIdException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_MERCHANT_ID_INVALID);
        $this->orderPayloadValidator->checkBaseNode($this->nodeProvider(''));
    }

    public function testOrderPayloadValidatorShippingNameException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_SHIPPING_NAME_INVALID);
        $this->orderPayloadValidator->checkShippingNode($this->shippingNodeProvider('0'));
    }

    public function testOrderPayloadValidatorShippingAddressException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_SHIPPING_ADDRESS_INVALID);
        $this->orderPayloadValidator->checkShippingNode($this->shippingNodeProvider('1'));
    }

    public function testOrderPayloadValidatorShippingCityException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_SHIPPING_CITY_INVALID);
        $this->orderPayloadValidator->checkShippingNode($this->shippingNodeProvider('2'));
    }

    public function testOrderPayloadValidatorShippingCountryCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $node = $this->shippingNodeProvider('3');
        $code = $node['shipping']['address']['country_code'];
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_SHIPPING_COUNTRY_CODE_INVALID);
        $this->orderPayloadValidator->checkShippingNode($this->shippingNodeProvider('3'));
    }

    public function testOrderPayloadValidatorShippingPostalCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_SHIPPING_POSTAL_CODE_INVALID);
        $this->orderPayloadValidator->checkShippingNode($this->shippingNodeProvider('4'));
    }

    public function testOrderPayloadValidatorPayerGivenNameException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_GIVEN_NAME_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('0'));
    }

    public function testOrderPayloadValidatorPayerSurnameException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_SURNAME_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('1'));
    }

    public function testOrderPayloadValidatorPayerEmailAddressException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_EMAIL_ADDRESS_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('2'));
    }

    public function testOrderPayloadValidatorPayerStreetAddressException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_STREET_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('3'));
    }

    public function testOrderPayloadValidatorPayerCityAddressException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_CITY_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('4'));
    }

    public function testOrderPayloadValidatorPayerCountryCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_COUNTRY_CODE_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('5'));
    }

    public function testOrderPayloadValidatorPayerPostalCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_POSTAL_CODE_INVALID);
        $this->orderPayloadValidator->checkPayerNode($this->payerNodeProvider('6'));
    }

    public function testOrderPayloadValidatorApplicationContextBrandNameException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_BRAND_NAME_INVALID);
        $this->orderPayloadValidator->checkApplicationContextNode($this->applicationContextNodeProvider('0'));
    }

    public function testOrderPayloadValidatorApplicationContextShippingPreferenceException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_SHIPPING_PREFERENCE_INVALID);
        $this->orderPayloadValidator->checkApplicationContextNode($this->applicationContextNodeProvider('1'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemNameException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('0'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemNameSkuException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_ORDER_NOT_FOUND);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('1'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemUnitAmountCurrencyCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_CURRENCY);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('2'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemUnitAmountValueException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_VALUE);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('3'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemTaxCurrencyCodeException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_CURRENCY);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('4'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemTaxValueException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_VALUE);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('5'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemQuantityException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_QUANTITY);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('6'));
    }

    public function testOrderPayloadValidatorAmountBreakDownItemCategoryException()
    {
        $this->expectException(OrderValidationException::class);
        $this->expectExceptionCode(OrderValidationException::PSCHECKOUT_ITEM_INVALID_CATEGORY);
        $this->orderPayloadValidator->checkAmountBreakDownNode($this->amountBreakDownNodeProvider('7'));
    }

    public function nodeProvider($value)
    {
        return [
            'intent' => $value == 'FAILURE' ? $value : 'CAPTURE', // capture or authorize
            'custom_id' => $value == '123' ? $value : 'abcd', // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => $value == 2 ? $value : '',
            'description' => $value == 3 ? $value : 'Checking out with your cart abcd from  ShopName',
            'amount' => [
                'currency_code' => $value == 'XXX' ? $value : 'EUR',
                'value' => $value == -1 ? $value : 123,
            ],
            'payee' => [
                'merchant_id' => $value == '' ? $value : 'ABCD',
            ],
        ];
    }

    public function shippingNodeProvider($i)
    {
        $node = ['0' => ['shipping' => [
            'name' => [
                'full_name' => '',
            ],
            'address' => [
                'address_line_1' => 'Kalno 5',
                'address_line_2' => 'Taraku 4',
                'admin_area_1' => 'Lithuania',
                'admin_area_2' => 'Kaunas',
                'country_code' => 'LT',
                'postal_code' => '50286',
            ],
        ]],
            '1' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => '',
                    'address_line_2' => 'Taraku 39',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50280',
                ],
            ]],
            '2' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 480',
                    'address_line_2' => 'Slieku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => '',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '3' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'XX',
                    'postal_code' => '50285',
                ],
            ]],
            '4' => ['shipping' => [
                'name' => [
                    'full_name' => 'Jonas',
                ],
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LU',
                    'postal_code' => '',
                ],
            ]],
        ];

        return $node[$i];
    }

    public function payerNodeProvider($i)
    {
        $node = ['0' => ['payer' => [
            'name' => [
                'given_name' => '',
                'surname' => 'Lennon',
            ],
            'email_address' => 'foo@bar.com',
            'address' => [
                'address_line_1' => 'Kalno 5',
                'address_line_2' => 'Taraku 4',
                'admin_area_1' => 'Lithuania',
                'admin_area_2' => 'Kaunas',
                'country_code' => 'LT',
                'postal_code' => '50286',
            ],
        ]],
            '1' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => '',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'klinciu 2',
                    'address_line_2' => 'Taraku 39',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50280',
                ],
            ]],
            '2' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => '',
                'address' => [
                    'address_line_1' => 'Malku 480',
                    'address_line_2' => 'Slieku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '3' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => '',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'LT',
                    'postal_code' => '50285',
                ],
            ]],
            '4' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => '',
                    'country_code' => 'LT',
                    'postal_code' => '56023',
                ],
            ]],
            '5' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Kaunas',
                    'country_code' => 'XX',
                    'postal_code' => '56023',
                ],
            ]],
            '6' => ['payer' => [
                'name' => [
                    'given_name' => 'John',
                    'surname' => 'Lennon',
                ],
                'email_address' => 'foo@bar.com',
                'address' => [
                    'address_line_1' => 'Malku 48',
                    'address_line_2' => 'Taraku 3',
                    'admin_area_1' => 'Lithuania',
                    'admin_area_2' => 'Vilnius',
                    'country_code' => 'LT',
                    'postal_code' => '',
                ],
            ]],
        ];

        return $node[$i];
    }

    public function applicationContextNodeProvider($i)
    {
        $node =
            ['0' => ['application_context' => [
                'brand_name' => '',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ],
            ],
                '1' => ['application_context' => [
                    'brand_name' => 'MyShop',
                    'shipping_preference' => '',
                ],
                ],
            ];

        return $node[$i];
    }

    public function amountBreakDownNodeProvider($i)
    {
        switch ($i) {
            case '0':
                return [
                    'items' => [
                        '0' => [
                            'name' => '',
                            'description' => 'Apie nieka',
                            'sku' => 'demo_12',
                            'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                            'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                            'quantity' => '1',
                            'category' => 'gems',
                        ],
                    ],
                ];
            case '1':
                return [
                    'items' => ['1' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => '',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '2':
                return [
                    'items' => ['2' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'XX', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '3':
                return [
                    'items' => ['3' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => ''],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '4':
                return [
                    'items' => ['4' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => '', 'value' => '2.3'],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '5':
                return [
                    'items' => ['5' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => ''],
                        'quantity' => '1',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '6':
                return [
                    'items' => ['6' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '2.9'],
                        'quantity' => '',
                        'category' => 'gems',
                    ],
                    ],
                ];
            case '7':
                return [
                    'items' => ['7' => [
                        'name' => 'John',
                        'description' => 'Apie nieka',
                        'sku' => 'demo_12',
                        'unit_amount' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'tax' => ['currency_code' => 'EUR', 'value' => '12.9'],
                        'quantity' => '12',
                        'category' => '',
                    ],
                    ],
                ];
        }

        return true;
    }
}
