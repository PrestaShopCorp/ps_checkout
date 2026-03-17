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
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateAction;
use PsCheckout\Core\OrderState\Action\SetRefundedOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCache;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\PayPal\Refund\Provider\PayPalRefundOrderProvider;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalRefundOrderFactory;

class SetRefundedOrderStateActionTest extends BaseTestCase
{
    /** @var PayPalRefundOrderProvider&\PHPUnit\Framework\MockObject\MockObject */
    private $payPalRefundOrderProvider;

    /** @var PayPalOrderProvider&\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderProvider;

    /** @var PayPalOrderCache&\PHPUnit\Framework\MockObject\MockObject */
    private $orderPayPalCache;

    /** @var OrderStateMapper */
    private $orderStateMapper;

    /** @var SetRefundedOrderStateAction */
    private $refundedOrderStateAction;

    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
        $this->payPalRefundOrderProvider = $this->createMock(PayPalRefundOrderProvider::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProvider::class);
        $this->orderPayPalCache = $this->createMock(PayPalOrderCache::class);
        /** @var OrderStateMapper $orderStateMapper */
        $orderStateMapper = $this->getService(OrderStateMapper::class);
        $this->orderStateMapper = $orderStateMapper;

        $this->refundedOrderStateAction = new SetRefundedOrderStateAction(
            $this->payPalRefundOrderProvider,
            $this->payPalOrderProvider,
            $this->orderStateMapper,
            $this->getService(ChangeOrderStateAction::class),
            $this->orderPayPalCache
        );
    }

    public function testSetRefundedOrderStateActionToFullRefund()
    {
        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '29.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '29.00']);
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }

    public function testSetRefundedOrderStateActionToPartiallyRefund()
    {
        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '15.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '30.00']);

        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }

    public function testAuthorizationFullRefundOfPartialCaptureIsPartiallyRefunded(): void
    {
        $payPalOrderResponseData = [
            'intent' => 'AUTHORIZE',
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'id' => 'CAPTURE-1',
                        'status' => 'REFUNDED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '10.00',
                        ],
                    ]],
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '10.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '22.94']);
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id, 'totalPaid' => 22.94]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }

    public function testAuthorizationFullRefundOfFullCaptureIsRefunded(): void
    {
        $payPalOrderResponseData = [
            'intent' => 'AUTHORIZE',
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'id' => 'CAPTURE-1',
                        'status' => 'REFUNDED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '22.94',
                        ],
                    ]],
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '22.94',
                        ],
                    ]],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '22.94']);
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id, 'totalPaid' => 22.94]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }

    public function testAuthorizationPartialRefundOfPartialCaptureIsPartiallyRefunded(): void
    {
        $payPalOrderResponseData = [
            'intent' => 'AUTHORIZE',
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
                        'id' => 'CAPTURE-1',
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '10.00',
                        ],
                    ]],
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '5.00',
                        ],
                    ]],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '22.94']);
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id, 'totalPaid' => 22.94]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }

    public function testSetRefundedOrderStateActionToFullRefundWithMultipleRefunds()
    {
        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '15.00',
                        ],
                    ],
                    [
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '14.00',
                        ],
                    ],
                    ],
                ],
            ]],
        ];

        $order = OrderFactory::create(['current_state' => 1, 'total_paid' => '30.00']);
        $payPalOrderResponse = PayPalOrderResponseFactory::create($payPalOrderResponseData);
        $payPalRefundOrder = PayPalRefundOrderFactory::create(['id' => $order->id]);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($payPalOrderResponse);

        $this->payPalRefundOrderProvider->expects($this->once())
            ->method('provide')
            ->willReturn($payPalRefundOrder);

        $expectedOrderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED);

        try {
            $this->refundedOrderStateAction->execute($payPalOrderResponse->getId());
        } catch (Exception $exception) {
            if ($exception->getCode() === OrderException::FAILED_UPDATE_ORDER_STATUS) {
            }
        }

        $this->assertEquals($expectedOrderStateId, (new \Order($order->id))->current_state);
    }
}
