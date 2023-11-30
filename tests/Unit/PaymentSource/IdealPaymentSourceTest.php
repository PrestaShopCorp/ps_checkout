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

class IdealPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidIdealDataProvider
     */
    public function testInvalidIdealPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'ideal',
            'Ideal',
            [
                new AmountEligibilityRule($data['amount'], '0.01'),
                new CountryEligibilityRule($data['buyerCountry'], ['NL']),
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

    public function invalidIdealDataProvider()
    {
        return [
            [
                [
                    'amount' => '0', // Invalid amount
                    'buyerCountry' => 'NL',
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
                    'buyerCountry' => 'NL',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product' // Invalid pageType
                ],
            ]
        ];
    }
}
