<?php

namespace Tests\Unit\PaymentSource;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;
use PrestaShop\Module\PrestashopCheckout\Currency\Exception\CurrencyException;
use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;
use PrestaShop\Module\PrestashopCheckout\Intent\ValueObject\Intent;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\AmountEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;
use PrestaShop\Module\PrestashopCheckout\PaymentSourceUseCase\Exception\PaymentSourceUseCaseException;
use PrestaShop\Module\PrestashopCheckout\Rule\AndRule;
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;
use PrestaShop\Module\PrestashopCheckout\Rule\OrRule;

class P24PaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidP24DataProvider
     *
     * @throws CurrencyException
     * @throws CountryException
     * @throws IntentException
     * @throws PaymentSourceUseCaseException
     */
    public function testInvalidP24PaymentSource($data, $paymentSourceRulesExpected, $paymentSourceUseCaseExpected)
    {
        $paymentSource = new PaymentSource(
            'p24',
            'Przelewy24',
            [
                new OrRule(
                    [
                        new AndRule(
                            [
                                new AmountEligibilityRule($data['amount'], '1'),
                                new CurrencyEligibilityRule($data['currency'], ['PLN']),
                            ]
                        ),
                        new AndRule(
                            [
                                // TODO : on a pas de valeur pour EUR
                                new AmountEligibilityRule($data['amount'], '0.50'),
                                new CurrencyEligibilityRule($data['currency'], ['EUR']),
                            ]
                        ),
                    ]
                ),
                new CountryEligibilityRule($data['buyerCountry'], ['PL']),
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

    public function invalidP24DataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    OrRule::class => false,
                    CountryEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                    'ECM',
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
                [
                    OrRule::class => true,
                    CountryEligibilityRule::class => false,
                    NotRule::class => true,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'PL',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    OrRule::class => false,
                    CountryEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'PL',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
                [
                    OrRule::class => true,
                    CountryEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'PL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout',
                ],
                [
                    OrRule::class => true,
                    CountryEligibilityRule::class => true,
                    NotRule::class => false,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'PL',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product', // Invalid pageType
                ],
                [
                    OrRule::class => true,
                    CountryEligibilityRule::class => true,
                    NotRule::class => true,
                ],
                [
                ],
            ],
        ];
    }
}
