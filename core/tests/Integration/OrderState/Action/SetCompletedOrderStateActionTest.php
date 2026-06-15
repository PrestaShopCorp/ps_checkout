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

namespace PsCheckout\Core\Tests\Integration\OrderState\Action;

use Exception;
use Order;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\Order\Validator\OrderAmountValidator;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateAction;
use PsCheckout\Core\OrderState\Action\SetCompletedOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Infrastructure\Repository\OrderRepository;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class SetCompletedOrderStateActionTest extends BaseTestCase
{
    /** @var SetCompletedOrderStateAction */
    private $setCompletedOrderStateAction;

    /** @var OrderStateMapper */
    private $orderStateMapper;

    /** @var PayPalOrderRepository */
    private $payPalOrderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setCompletedOrderStateAction = $this->getService(SetCompletedOrderStateAction::class);
        $this->orderStateMapper = $this->getService(OrderStateMapper::class);
        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);

        $this->payPalOrderProviderMock = $this->getMockBuilder(PayPalOrderProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setCompletedOrderStateAction = new SetCompletedOrderStateAction(
            $this->payPalOrderRepository,
            $this->orderRepositoryMock,
            $this->getService(OrderAmountValidator::class),
            $this->orderStateMapper,
            $this->getService(ChangeOrderStateAction::class),
            $this->payPalOrderProviderMock
        );
    }

    public function testItShouldChangeStateToCompleted(): void
    {
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        // Create a mock for PayPalOrderProviderInterface
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldChangeStateToPartialyPaid(): void
    {
        // NOTE : Order total paid more than paid amount
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '15.00',
                        ],
                    ]],
                ],
            ]],
        ];

        // Create a mock for PayPalOrderProviderInterface
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldChangeStateToCompletedWithMultiplePartialCaptures(): void
    {
        $partiallyPaidStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID);
        $order = OrderFactory::create(['total_paid' => 34.80, 'current_state' => $partiallyPaidStateId]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [
                        [
                            'status' => 'COMPLETED',
                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => '14.80',
                            ],
                        ],
                        [
                            'status' => 'COMPLETED',
                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => '20.00',
                            ],
                        ],
                    ],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldNotChangeStateWhenOrderHasAlreadyBeenCompleted(): void
    {
        $completedStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);
        $order = OrderFactory::create(['total_paid' => 29.00]);

        // Insert history directly rather than via setCurrentState() — the latter triggers email
        // sending which requires the Symfony kernel/container, unavailable in the PHPUnit context.
        \Db::getInstance()->insert('order_history', [
            'id_order' => $order->id,
            'id_order_state' => $completedStateId,
            'id_employee' => 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);

        // Simulate that order has been moved past Completed by another module
        $shippedStateId = 4; // PS default "Shipped" state
        \Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'orders` SET `current_state` = ' . $shippedStateId . ' WHERE `id_order` = ' . (int) $order->id
        );
        \Db::getInstance()->insert('order_history', [
            'id_order' => $order->id,
            'id_order_state' => $shippedStateId,
            'id_employee' => 0,
            'date_add' => date('Y-m-d H:i:s'),
        ]);
        $order->current_state = $shippedStateId;

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        // Order should remain in Shipped state, not regress to Completed
        $this->assertEquals($shippedStateId, (int) (new \Order($order->id))->current_state);
    }

    public function testItShouldChangeStateToCompletedWhenCaptureIsPartiallyRefunded(): void
    {
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'PARTIALLY_REFUNDED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldChangeStateToCompletedWhenCaptureIsFullyRefunded(): void
    {
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'REFUND',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldNotCountPendingCapturesInTotal(): void
    {
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [
                        [
                            'status' => 'COMPLETED',
                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => '15.00',
                            ],
                        ],
                        [
                            'status' => 'PENDING',
                            'amount' => [
                                'currency_code' => 'EUR',
                                'value' => '14.00',
                            ],
                        ],
                    ],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldChangeStateToCompletedWhenOverpaid(): void
    {
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '35.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_order' => $order->id,
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn($order);

        $expectedStatus = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED);

        try {
            $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $e) {
            if ($e->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
                // NOTE: Email sending causes this error
            }
        }

        $this->assertEquals($expectedStatus, (new \Order($order->id))->current_state);
    }

    public function testItShouldReturnEarlyWhenOrderNotFound(): void
    {
        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);

        $this->payPalOrderRepository->save(PayPalOrderFactory::create([
            'id_paypal_order' => $payPalOrderResponse->getId(),
        ]));

        $this->payPalOrderProviderMock->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->orderRepositoryMock->expects($this->once())
            ->method('getOneBy')
            ->willReturn(null);

        // Should not throw any exception
        $this->setCompletedOrderStateAction->execute($payPalOrderResponse->getId());
    }

    public function testItShouldThrowPayPalOrderDoesNotExistException(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found.');

        $this->setCompletedOrderStateAction->execute('non-existing-id');
    }
}
