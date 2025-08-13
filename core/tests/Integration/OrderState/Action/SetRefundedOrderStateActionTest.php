<?php

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
    protected function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
        $this->payPalRefundOrderProvider = $this->createMock(PayPalRefundOrderProvider::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProvider::class);
        $this->orderPayPalCache = $this->createMock(PayPalOrderCache::class);
        $this->orderStateMapper = $this->getService(OrderStateMapper::class);

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
                            'value' => '29.00'
                        ],
                    ]]
                ]
            ]]
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
                            'value' => '15.00'
                        ],
                    ]]
                ]
            ]]
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

    public function testSetRefundedOrderStateActionToFullRefundWithMultipleRefunds()
    {
        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'refunds' => [[
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '15.00'
                        ],
                    ],
                    [
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '14.00'
                        ],
                    ],
                    ]
                ]
            ]]
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
