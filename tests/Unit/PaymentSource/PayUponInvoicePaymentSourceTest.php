<?php

namespace Tests\Unit\PaymentSource;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;
use PrestaShop\Module\PrestashopCheckout\Intent\ValueObject\Intent;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\AmountEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CountryEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\CurrencyEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\IntentEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule\PageTypeEligibilityRule;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSource;
use PrestaShop\Module\PrestashopCheckout\PaymentSource\PaymentSourceUseCase;

class PayUponInvoicePaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidPayUponInvoiceDataProvider
     *
     * @throws IntentException
     */
    public function testInvalidPayUponInvoicePaymentSource($data, $paymentSourceRulesExpected, $paymentSourceUseCaseExpected)
    {
        $paymentSource = new PaymentSource(
            'payuponinvoice',
            'PayUponInvoice',
            [
                new AmountEligibilityRule($data['amount'], '5', '2500'),
                new CountryEligibilityRule($data['buyerCountry'], ['DE']),
                new CurrencyEligibilityRule($data['currency'], ['EUR']),
                new CountryEligibilityRule($data['merchantCountry'], ['DE']),
            ],
            [
                new PaymentSourceUseCase(
                    'ECM',
                    [
                        new B2BEligibilityRule($data['B2B'], false),
                        new B2CEligibilityRule($data['B2C'], true),
                        new IntentEligibilityRule(new Intent($data['intent']), ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                        new VirtualGoodsEligibilityRule($data['virtualGoods'], false),
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

    public function invalidPayUponInvoiceDataProvider()
    {
        return [
            [
                [
                    'amount' => '3', // Invalid amount < minimal
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '2600', // Invalid amount > maximal
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => true, // Invalid B2B
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => false, // Invalid B2C
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'FR', // Invalid buyerCountry
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'USD', // Invalid currency
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'AUTHORIZE', // Invalid intent
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'FR', // Invalid merchantCountry
                    'pageType' => 'checkout',
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'cart', // Invalid pageType
                    'virtualGoods' => false,
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
                    'amount' => '239.99',
                    'B2B' => false,
                    'B2C' => true,
                    'buyerCountry' => 'DE',
                    'currency' => 'EUR',
                    'intent' => 'CAPTURE',
                    'merchantCountry' => 'DE',
                    'pageType' => 'checkout',
                    'virtualGoods' => true, // Invalid virtualGoods
                ],
            ], [
                true,
                true,
                true,
                true,
            ],
            [
            ],
        ];
    }
}
