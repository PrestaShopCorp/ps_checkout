<?php

namespace Tests\Unit\Amount;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\AmountEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\ExcludedCountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;

class BlikPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidBlikDataProvider
     */
    public function testInvalidBlikPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'blik',
            'Blik',
            [
                new AmountEligibilityRule($data['amount'], '1'),
                new CountryEligibilityRule($data['buyerCountry'], ['PL']),
                new CurrencyEligibilityRule($data['currency'], ['PLN']),
                new ExcludedCountryEligibilityRule($data['merchantCountry'], ['RU', 'JP', 'BR']),
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

    public function invalidBlikDataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'FR', // Invalid buyer country
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'PL',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product' // Invalid pageType
                ],
            ]
        ];
    }
}
