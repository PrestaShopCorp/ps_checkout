<?php

namespace Tests\Unit\Amount;

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

class PaypalPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider payPalDataProvider
     *
     * @throws PaymentSourceUseCaseException
     * @throws IntentException
     * @throws CountryException
     * @throws CurrencyException
     */
    public function testInvalidPayPalPaymentSource($data, $paymentSourceRules, $paymentSourceUseCase)
    {
        $paymentSource = new PaymentSource(
            'PayPal',
            'PayPal',
            [
                new CountryEligibilityRule($data['buyerCountry'], ['AT', 'CA', 'DK', 'FI', 'FR', 'EE', 'DE', 'IT', 'LT', 'NL', 'ES', 'UK', 'US']),
                new CurrencyEligibilityRule($data['currency'], ['EUR', 'USD', 'AUD', 'GBP']),
            ],
            [
                new PaymentSourceUseCase(
                    'PayPal',
                    [
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                    ]
                ),
                new PaymentSourceUseCase(
                    'ECS',
                    [
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['cart', 'product', 'authentication']),
                    ]
                ),
                new PaymentSourceUseCase(
                    'Vaulting',
                    [
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                    ]
                ),
            ]
        );
        $this->rulesTesting($paymentSource->getRules(), $paymentSourceRules);
        $this->UseCasesTesting($paymentSource->getUseCases(), $paymentSourceUseCase);
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

    public function payPalDataProvider()
    {
        return [
            [
                [
                    'buyerCountry' => 'FR',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                ],
                [
                    'PayPal',
                    'Vaulting',
                ],
            ],
            [
                [
                    'buyerCountry' => 'BE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
                [
                    CountryEligibilityRule::class => false,
                    CurrencyEligibilityRule::class => true,
                ],
                [
                    'PayPal',
                    'Vaulting',
                ],
            ],
            [
                [
                    'buyerCountry' => 'FR',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'cart',
                ],
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                ],
                [
                    'ECS',
                ],
            ],
            [
                [
                    'buyerCountry' => 'FR',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'product',
                ],
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                ],
                [
                    'ECS',
                ],
            ],
            [
                [
                    'buyerCountry' => 'FR',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'authentication',
                ],
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                ],
                [
                    'ECS',
                ],
            ],
            [
                [
                    'buyerCountry' => 'FR',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE',
                    'merchantCountry' => 'FR',
                    'pageType' => 'checkout',
                ],
                [
                    CountryEligibilityRule::class => true,
                    CurrencyEligibilityRule::class => true,
                ],
                [],
            ],
        ];
    }
}
