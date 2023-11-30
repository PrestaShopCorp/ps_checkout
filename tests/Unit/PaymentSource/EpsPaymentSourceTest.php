<?php

namespace Tests\Unit\Amount;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\AmountEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;

class EpsPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidEpsDataProvider
     */
    public function testInvalidEpsPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'eps',
            'Eps',
            [
                new AmountEligibilityRule($data['amount'], '1'),
                new CountryEligibilityRule($data['buyerCountry'], ['AT']),
                new CurrencyEligibilityRule($data['currency'], ['EUR']),
                new NotRule(new CountryEligibilityRule($data['merchantCountry'], ['RU', 'JP', 'BR'])),
            ],
            [
                new PaymentSourceUseCase(
                    'ECM',
                    [
                        new IntentEligibilityRule($data['intent'], ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout'])
                    ]
                )
            ]
        );
    }

    public function invalidEpsDataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'AT',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'FR', // Invalid buyer country
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'AT',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'AT',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'AT',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'AT',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product' // Invalid pageType
                ],
            ]
        ];
    }
}
