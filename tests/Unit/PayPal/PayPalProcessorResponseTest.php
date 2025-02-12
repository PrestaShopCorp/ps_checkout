<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Tests\Unit\PayPal;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;

class PayPalProcessorResponseTest extends TestCase
{
    /**
     * @dataProvider orderProvider
     */
    public function testContinueWithAuthorization(array $order)
    {
        $cardBrand = isset($order['payment_source']['card']['brand']) ? $order['payment_source']['card']['brand'] : null;
        $cardType = isset($order['payment_source']['card']['type']) ? $order['payment_source']['card']['type'] : null;
        $avsCode = isset($order['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code']) ? $order['purchase_units'][0]['payments']['captures'][0]['processor_response']['avs_code'] : null;
        $cvvCode = isset($order['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code']) ? $order['purchase_units'][0]['payments']['captures'][0]['processor_response']['cvv_code'] : null;
        $paymentAdviceCode = isset($order['purchase_units'][0]['payments']['captures'][0]['processor_response']['payment_advice_code']) ? $order['purchase_units'][0]['payments']['captures'][0]['processor_response']['payment_advice_code'] : null;
        $responseCode = isset($order['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code']) ? $order['purchase_units'][0]['payments']['captures'][0]['processor_response']['response_code'] : null;
        $processorResponse = new PayPalProcessorResponse(
            $cardBrand,
            $cardType,
            $avsCode,
            $cvvCode,
            $paymentAdviceCode,
            $responseCode
        );
        $this->assertEquals($cardBrand, $processorResponse->getCardBrand());
        $this->assertEquals($cardType, $processorResponse->getCardType());
        $this->assertEquals($avsCode, $processorResponse->getAvsCode());
        $this->assertEquals($cvvCode, $processorResponse->getCvvCode());
        $this->assertEquals($paymentAdviceCode, $processorResponse->getPaymentAdviceCode());
        $this->assertEquals($responseCode, $processorResponse->getResponseCode());
        if ($avsCode) {
            $this->assertNotEmpty($processorResponse->getAvsCodeDescription());
        }
        if ($cvvCode) {
            $this->assertNotEmpty($processorResponse->getCvvCodeDescription());
        }
        if ($paymentAdviceCode) {
            $this->assertNotEmpty($processorResponse->getPaymentAdviceCodeDescription());
        }
        if ($responseCode) {
            $this->assertNotEmpty($processorResponse->getResponseCodeDescription());
        }
    }

    public function orderProvider()
    {
        return [
            [[
                'payment_source' => [
                    'card' => [
                        'brand' => 'MASTERCARD',
                        'type' => 'PREPAID',
                    ],
                ],
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'processor_response' => [
                                        'avs_code' => 'S',
                                        'cvv_code' => 'D',
                                        'response_code' => '9510',
                                        'payment_advice_code' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            [[
                'payment_source' => [
                    'card' => [
                        'brand' => 'MASTERCARD',
                        'type' => 'PREPAID',
                    ],
                ],
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'processor_response' => [
                                        'avs_code' => 'Z',
                                        'cvv_code' => 'N',
                                        'response_code' => '00N7',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            [[
                'payment_source' => [
                    'card' => [
                        'brand' => 'MASTERCARD',
                        'type' => 'PREPAID',
                    ],
                ],
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'processor_response' => [
                                        'avs_code' => 'I',
                                        'cvv_code' => 'N',
                                        'response_code' => '5120',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
            [[
                'payment_source' => [
                    'card' => [
                        'brand' => 'MASTERCARD',
                        'type' => 'PREPAID',
                    ],
                ],
                'purchase_units' => [
                    [
                        'payments' => [
                            'captures' => [
                                [
                                    'processor_response' => [
                                        'avs_code' => 'U',
                                        'cvv_code' => 'M',
                                        'response_code' => '5650',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]],
        ];
    }
}
