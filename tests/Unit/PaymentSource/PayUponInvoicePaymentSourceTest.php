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

class PayUponInvoicePaymentSourceTest extends TestCase
{
    /**
     * @dataProvider invalidPayUponInvoiceDataProvider
     */
    public function testInvalidPayUponInvoicePaymentSource($data)
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
                        new IntentEligibilityRule($data['intent'], ['CAPTURE']),
                        new PageTypeEligibilityRule($data['pageType'], ['checkout']),
                        new VirtualGoodsEligibilityRule($data['virtualGoods'], false),
                    ]
                ),
            ]
        );
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
            ],
        ];
    }
}
