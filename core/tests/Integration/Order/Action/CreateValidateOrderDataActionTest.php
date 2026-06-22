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

namespace PsCheckout\Core\Tests\Integration\Order\Action;

use Cart;
use PsCheckout\Core\Order\Action\CreateValidateOrderDataAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class CreateValidateOrderDataActionTest extends BaseTestCase
{
    private $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createValidateOrderDataAction = $this->getService(CreateValidateOrderDataAction::class);
        $this->context = $this->getService(Context::class);

        /** @var PayPalOrderRepository $payPalOrderRepository */
        $payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $payPalOrderRepository->save(PayPalOrderFactory::create());
    }

    /**
     * @dataProvider provideOrderData
     */
    public function testCreateValidateOrderData(array $data): void
    {
        if (isset($data['expectException'])) {
            $this->expectException($data['expectException']);
            $this->expectExceptionCode($data['expectExceptionCode']);
        }

        // Create mock cart
        /** @var Cart $cart */
        $cart = new \Cart();

        // Set cart properties
        $cart->id = 1;
        $cart->id_lang = 1;
        $cart->id_currency = 1; // EUR
        $cart->id_shop = 1;
        $cart->total = $data['cart_total'];
        $cart->secure_key = 'test-secure-key';

        // Set cart in context
        $this->context->setCurrentCart($cart);

        // Create PayPal order response
        $payPalOrderResponse = PayPalOrderResponseFactory::create($data['payPalOrderResponse'] ?? []);

        // Execute the action
        $result = $this->createValidateOrderDataAction->execute($payPalOrderResponse);

        // Verify results
        $this->assertEquals($cart->id, $result->getCartId());
        $this->assertEquals($cart->secure_key, $result->getSecureKey());
        $this->assertEquals($data['expectedAmount'], $result->getPaidAmount());
        $this->assertEquals($data['expectedCurrencyId'], $result->getCurrencyId());
        $this->assertEquals($data['expectedFundingSource'], $result->getFundingSource());

        // Verify transaction ID if expected
        if (isset($data['expectedTransactionId'])) {
            $this->assertEquals($data['expectedTransactionId'], $result->getExtraVars()['transaction_id']);
        }
    }

    public function provideOrderData(): array
    {
        return [
            'fully paid order should be set to completed state' => [
                [
                    'cart_total' => '29.00',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
                    'expectedAmount' => '29.00',
                    'expectedCurrencyId' => 1,
                    'expectedFundingSource' => 'paypal',
                    'expectedTransactionId' => 'TEST-CAPTURE-123',
                    'payPalOrderResponse' => [
                        'purchase_units' => [
                            [
                                'payments' => [
                                    'captures' => [
                                        [
                                            'id' => 'TEST-CAPTURE-123',
                                            'status' => 'COMPLETED',
                                            'amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'partially paid order should be set to partially paid state' => [
                [
                    'cart_total' => '29.00',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID,
                    'expectedAmount' => '15.00',
                    'expectedCurrencyId' => 1,
                    'expectedFundingSource' => 'paypal',
                    'expectedTransactionId' => 'TEST-CAPTURE-123',
                    'payPalOrderResponse' => [
                        'purchase_units' => [
                            [
                                'payments' => [
                                    'captures' => [
                                        [
                                            'id' => 'TEST-CAPTURE-123',
                                            'status' => 'COMPLETED',
                                            'amount' => ['currency_code' => 'EUR', 'value' => '15.00'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'overpaid order should be set to completed state' => [
                [
                    'cart_total' => '29.00',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
                    'expectedAmount' => '35.00',
                    'expectedCurrencyId' => 1,
                    'expectedFundingSource' => 'paypal',
                    'expectedTransactionId' => 'TEST-CAPTURE-123',
                    'payPalOrderResponse' => [
                        'purchase_units' => [
                            [
                                'payments' => [
                                    'captures' => [
                                        [
                                            'id' => 'TEST-CAPTURE-123',
                                            'status' => 'COMPLETED',
                                            'amount' => ['currency_code' => 'EUR', 'value' => '35.00'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'pending order should be set to pending state' => [
                [
                    'cart_total' => '29.00',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING,
                    'expectedAmount' => '0.0',
                    'expectedCurrencyId' => 1,
                    'expectedFundingSource' => 'paypal',
                    'payPalOrderResponse' => [
                        'purchase_units' => [
                            [
                                'payments' => [
                                    'captures' => [
                                        [
                                            'id' => 'TEST-CAPTURE-123',
                                            'status' => 'PENDING',
                                            'amount' => ['currency_code' => 'EUR', 'value' => '29.00'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'order with different currency should use correct currency ID' => [
                [
                    'cart_total' => '29.00',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
                    'expectedAmount' => '29.00',
                    'expectedCurrencyId' => 2, // USD
                    'expectedFundingSource' => 'paypal',
                    'expectedTransactionId' => 'TEST-CAPTURE-123',
                    'payPalOrderResponse' => [
                        'purchase_units' => [
                            [
                                'payments' => [
                                    'captures' => [
                                        [
                                            'id' => 'TEST-CAPTURE-123',
                                            'status' => 'COMPLETED',
                                            'amount' => ['currency_code' => 'USD', 'value' => '29.00'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
