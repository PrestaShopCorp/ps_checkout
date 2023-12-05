<?php

namespace Tests\Unit\Amount;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;

class MyBankPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidMyBankDataProvider
     */
    public function testInvalidMyBankPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'mybank',
            'MyBank',
            [
                new CountryEligibilityRule($data['buyerCountry'], ['IT']),
                new CurrencyEligibilityRule($data['currency'], ['EUR']),
                new NotRule(new CountryEligibilityRule($data['merchantCountry'], ['RU', 'JP', 'BR'])),
            ],
            [
                new PaymentSourceUseCase(
                    'ECM',
                    [
                        new IntentEligibilityRule($data['intent'], ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                    ]
                ),
            ]
        );
    }

    public function invalidMyBankDataProvider()
    {
        return [
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'FR', // Invalid buyer country
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'IT',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'IT',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'IT',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'IT',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product', // Invalid pageType
                ],
            ],
        ];
    }
}
