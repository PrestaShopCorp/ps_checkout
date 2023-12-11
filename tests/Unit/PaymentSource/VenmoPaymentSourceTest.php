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

class VenmoPaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidVenmoDataProvider
     *
     * @param $data
     * @param $paymentSourceRulesExpected
     * @param $paymentSourceUseCaseExpected
     *
     * @throws CountryException
     * @throws CurrencyException
     * @throws PaymentSourceUseCaseException
     * @throws IntentException
     */
    public function testInvalidVenmoPaymentSource($data, $paymentSourceRulesExpected, $paymentSourceUseCaseExpected)
    {
        $paymentSource = new PaymentSource(
            'venmo',
            'Venmo',
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
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['authentication', 'cart', 'checkout', 'product']),
                    ]
                ),
            ]
        );
        $this->rulesTesting($paymentSource->getRules(), $paymentSourceRulesExpected);
        $this->UseCasesTesting($paymentSource->getUseCases(), $paymentSourceUseCaseExpected);
    }

    private function rulesTesting($rules, $resultExpected)
    {
        foreach ($rules as $key => $rule) {
            $this->assertEquals($rule->evaluate(), $resultExpected[$key]);
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

    public function invalidVenmoDataProvider()
    {
        return [
            [
                [
                    'amount' => '0.99', // Invalid amount
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    false,
                    true,
                    true,
                    true,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'FR', // Invalid buyer country
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    true,
                    false,
                    true,
                    true,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '9.90',
                    'buyerCountry' => 'US',
                    'currency' => 'EUR', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    true,
                    true,
                    false,
                    true,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'US',
                    'pageType' => 'checkout',
                ],
                [
                    true,
                    true,
                    true,
                    true,
                ],
                [
                ],
            ],
            [
                [
                    'amount' => '15',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR', // Invalid merchant country
                    'pageType' => 'checkout',
                ],
                [
                    true,
                    true,
                    true,
                    false,
                ],
                [
                    'ECM',
                ],
            ],
            [
                [
                    'amount' => '39.99',
                    'buyerCountry' => 'US',
                    'currency' => 'USD',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'US',
                    'pageType' => 'catalog', // Invalid pageType
                ],
                [
                    true,
                    true,
                    true,
                    true,
                ],
                [
                ],
            ],
        ];
    }
}
