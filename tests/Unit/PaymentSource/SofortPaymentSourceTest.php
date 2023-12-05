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
use PrestaShop\Module\PrestashopCheckout\Rule\AndRule;
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;
use PrestaShop\Module\PrestashopCheckout\Rule\OrRule;

class SofortPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidSofortDataProvider
     */
    public function testInvalidSofortPaymentSource($data)
    {
        $paymentSource = new PaymentSource(
            'sofort',
            'SOFORT',
            [
                new OrRule(
                    [
                        new AndRule(
                            [
                                new AmountEligibilityRule($data['amount'], '1'),
                                new CurrencyEligibilityRule($data['currency'], ['EUR']),
                            ]
                        ),
                        new AndRule(
                            [
                                // TODO : on a pas de valeur pour GBP
                                new AmountEligibilityRule($data['amount'], '1.10'),
                                new CurrencyEligibilityRule($data['currency'], ['GBP']),
                            ]
                        ),
                    ]
                ),
                new CountryEligibilityRule($data['buyerCountry'], ['AT', 'BE', 'DE', 'ES', 'NL', 'UK']),
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

    public function invalidSofortDataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
            ],
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
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'NL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product', // Invalid pageType
                ],
            ],
        ];
    }
}
