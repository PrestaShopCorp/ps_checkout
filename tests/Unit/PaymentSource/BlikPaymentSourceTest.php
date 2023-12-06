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
use PrestaShop\Module\PrestashopCheckout\Rule\NotRule;

class BlikPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidBlikDataProvider
     *
     * @throws IntentException
     * @throws CountryException
     * @throws CurrencyException|PaymentSourceUseCaseException
     */
    public function testInvalidBlikPaymentSource($data, $paymentSourceRulesExpected, $paymentSourceUseCaseExpected)
    {
        $paymentSource = new PaymentSource(
            'blik',
            'Blik',
            [
                new AmountEligibilityRule($data['amount'], '1'),
                new CountryEligibilityRule($data['buyerCountry'], ['PL']),
                new CurrencyEligibilityRule($data['currency'], ['PLN']),
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

    public function rulesTesting($rules, $resultExpected)
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
                    'pageType' => 'checkout',
                ],
                [
                    AmountEligibilityRule::class => false,
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
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
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    AmountEligibilityRule::class => true,
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
                    'buyerCountry' => 'PL',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    AmountEligibilityRule::class => true,
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
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
                [
                    AmountEligibilityRule::class => true,
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
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'JP', // Invalid merchant country
                    'pageType' => 'checkout',
                ],
                [
                    AmountEligibilityRule::class => true,
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
                    'buyerCountry' => 'PL',
                    'currency' => 'PLN',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product', // Invalid pageType
                ],
                [
                    AmountEligibilityRule::class => true,
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
