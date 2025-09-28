<?php

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
    private ?SetCompletedOrderStateAction $setCompletedOrderStateAction;
    private ?OrderStateMapper $orderStateMapper;
    private ?PayPalOrderRepository $payPalOrderRepository;

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
        //NOTE : Order total paid more than paid amount
        $order = OrderFactory::create(['total_paid' => 29.00]);

        $payPalOrderResponseData = [
            'purchase_units' => [[
                'payments' => [
                    'captures' => [[
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

    public function testItShouldThrowPayPalOrderDoesNotExistException(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found.');

        $this->setCompletedOrderStateAction->execute('non-existing-id');
    }
}
