<?php

namespace PsCheckout\Core\Tests\Integration\PaymentToken\Action;

use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenAction;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\CustomerFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Infrastructure\Repository\PaymentTokenRepository;
use PsCheckout\Infrastructure\Repository\PayPalCustomerRepository;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class SavePaymentTokenActionTest extends BaseTestCase
{
    private ?SavePaymentTokenAction $savePaymentTokenAction;
    private ?PayPalCustomerRepository $paypalCustomerRepository;
    private ?PaymentTokenRepository $paymentTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->savePaymentTokenAction = $this->getService(SavePaymentTokenAction::class);
        $this->paypalCustomerRepository = $this->getService(PayPalCustomerRepository::class);
        $this->paymentTokenRepository = $this->getService(PaymentTokenRepository::class);

        $this->customer = CustomerFactory::create();
        $this->context = \Context::getContext();
        $this->context->customer = $this->customer;
    }

    /**
     * @dataProvider provideVaultData
     */
    public function testSavePaymentTokenWithVaultData(array $vaultData, array $expectedTokenData): void
    {
        // Create PayPal order response with vault data
        $payPalOrderResponse = PayPalOrderResponseFactory::create(
            $vaultData
        );

        /** @var PayPalOrderRepository $paypalOrderRepository */
        $paypalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $paypalOrderRepository->save(PayPalOrderFactory::create(['id' => $vaultData['id'], 'customer_intent' => ['VAULT,USES_VAULTING']]));

        // Execute the action
        $this->savePaymentTokenAction->execute($payPalOrderResponse);

        $this->assertEquals(
            $vaultData['payment_source'][key($vaultData['payment_source'])]['attributes']['vault']['customer']['id'],
            $this->paypalCustomerRepository->getPayPalCustomerIdByCustomerId($this->customer->id)
        );
        // Verify payment token was saved
        $token = $this->paymentTokenRepository->getOneById($vaultData['payment_source'][key($vaultData['payment_source'])]['attributes']['vault']['id']);

        $this->assertInstanceOf(PaymentToken::class, $token);
        $this->assertEquals($expectedTokenData['id_token'], $token->getId());
        $this->assertEquals($expectedTokenData['paypal_customer_id'], $token->getPaypalCustomerId());
        $this->assertEquals($expectedTokenData['payment_source'], $token->getPaymentSource());
        $this->assertEquals($expectedTokenData['status'], $token->getStatus());
    }

    public function provideVaultData(): array
    {
        return [
            'card payment source with status VERIFIED' => [
                'vaultData' => [
                    'id' => 'TEST-ORDER-123',
                    'status' => 'COMPLETED',
                    'payment_source' => [
                        'card' => [
                            'attributes' => [
                                'vault' => [
                                    'id' => 'TEST-VAULT-123',
                                    'customer' => ['id' => 'TEST-CUSTOMER-123'],
                                    'status' => 'VERIFIED'
                                ]
                            ]
                        ],
                    ],
                ],
                'expectedTokenData' => [
                    'id_token' => 'TEST-VAULT-123',
                    'paypal_customer_id' => 'TEST-CUSTOMER-123',
                    'payment_source' => 'card',
                    'status' => 'VERIFIED'
                ]
            ],
            'paypal payment source with status ACTIVE' => [
                'vaultData' => [
                    'id' => 'TEST-ORDER-456',
                    'status' => 'COMPLETED',
                    'payment_source' => [
                        'paypal' => [
                            'attributes' => [
                                'vault' => [
                                    'id' => 'TEST-VAULT-456',
                                    'customer' => ['id' => 'TEST-CUSTOMER-456'],
                                    'status' => 'ACTIVE'
                                ]
                            ]
                        ],
                    ],
                ],
                'expectedTokenData' => [
                    'id_token' => 'TEST-VAULT-456',
                    'paypal_customer_id' => 'TEST-CUSTOMER-456',
                    'payment_source' => 'paypal',
                    'status' => 'ACTIVE'
                ]
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
} 