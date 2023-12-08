<?php

namespace Tests\Unit\PaymentSource;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;
use PrestaShop\Module\PrestashopCheckout\Currency\Exception\CurrencyException;
use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;
use PrestaShop\Module\PrestashopCheckout\Intent\ValueObject\Intent;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;
use PrestaShop\Module\PrestashopCheckout\PaymentSourceUseCase\Exception\PaymentSourceUseCaseException;
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;

class MyBankPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidMyBankDataProvider
     *
     * @throws IntentException
     * @throws PaymentSourceUseCaseException
     * @throws CountryException
     * @throws CurrencyException
     */
    public function testInvalidMyBankPaymentSource($data, $paymentSourceRulesExpected, $paymentSourceUseCaseExpected)
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
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                    ]
                ),
            ]
        );
        $this->rulesTesting($paymentSource->getRules(), $paymentSourceRulesExpected);
        $this->UseCasesTesting($paymentSource->getUseCases(), $paymentSourceUseCaseExpected);
    }

    private function rulesTesting($rules, $resultExpected)
    {
        foreach ($rules as $rule) {
            $this->assertEquals($rule->evaluate(), $resultExpected[get_class($rule)]);
        }
    }

    private function UseCasesTesting($useCases, $resultExpected)
    {
        foreach ($useCases as $useCase) {
            $isAvailableUseCase = true;
            foreach ($useCase->getRules() as $rule) {
                $isAvailableUseCase = $rule->evaluate();
                if (!$isAvailableUseCase) {
                    $this->assertFalse(in_array($useCase->getType(), $resultExpected));
                    break;
                }
            }
            if ($isAvailableUseCase) {
                $this->assertTrue(in_array($useCase->getType(), $resultExpected));
            }
        }
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
                [
                    CountryEligibilityRule::class => false,
                    CurrencyEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                    'ECM',
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
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => false,
                    NotRule::class => true,
                ],
                [
                    'ECM',
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
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
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
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                    NotRule::class => false,
                ],
                [
                    'ECM',
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
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                ],
            ],
        ];
    }
}
