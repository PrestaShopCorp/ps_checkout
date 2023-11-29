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

class GooglePayPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidGooglePayDataProvider
     */
    public function testInvalidGooglePayPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'googlepay',
            'Google Pay',
            [
                new AmountEligibilityRule($data['amount'], '1'),
                new CountryEligibilityRule($data['buyerCountry'], ['US']),
                new CurrencyEligibilityRule($data['currency'], ['USD']),
                new CountryEligibilityRule($data['merchantCountry'], ['US']),
            ],
            [
                new PaymentSourceUseCase(
                    'ECM',
                    [
                        new IntentEligibilityRule($data['intent'], ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['authentication', 'cart', 'checkout', 'product'])
                    ]
                )
            ]
        );
    }

    public function invalidGooglePayDataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'authentication'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'FR', // Invalid buyer country
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'cart'
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'US',
                    'currency' => 'EUR', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'US',
                    'pageType' => 'product'
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR', // Invalid merchant country
                    'pageType' => 'checkout'
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'index' // Invalid pageType
                ],
            ]
        ];
    }
}
