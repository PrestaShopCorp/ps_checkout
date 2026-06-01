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

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\Order\Action\CreateOrderPaymentActionInterface;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Order\Handler\PaymentCompletedEventHandler;

class PaymentCompletedEventHandlerTest extends TestCase
{
    /** @var CreateOrderActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $createOrderAction;

    /** @var CreateOrderPaymentActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $createOrderPaymentAction;

    /** @var SetOrderStateActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $setCompletedOrderStateAction;

    /** @var PaymentCompletedEventHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->createOrderAction = $this->createMock(CreateOrderActionInterface::class);
        $this->createOrderPaymentAction = $this->createMock(CreateOrderPaymentActionInterface::class);
        $this->setCompletedOrderStateAction = $this->createMock(SetOrderStateActionInterface::class);

        $this->handler = new PaymentCompletedEventHandler(
            $this->createOrderAction,
            $this->createOrderPaymentAction,
            $this->setCompletedOrderStateAction
        );
    }

    public function testHandleCallsAllThreeActionsOnHappyPath(): void
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('paypal-order-123');

        $this->createOrderAction->expects($this->once())->method('execute')->with($response);
        $this->createOrderPaymentAction->expects($this->once())->method('execute')->with($response);
        $this->setCompletedOrderStateAction->expects($this->once())->method('execute')->with('paypal-order-123');

        $this->handler->handle($response);
    }

    public function testHandleWhenOrderAlreadyExistsStillCallsPaymentAndStateActions(): void
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('paypal-order-123');

        // Simulate crash-then-retry: the PS order already exists from the first attempt.
        $this->createOrderAction
            ->method('execute')
            ->willThrowException(new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS));

        // createOrderPaymentAction and setCompletedOrderStateAction must still be called —
        // both are idempotent and must run to ensure the payment record and state are set.
        $this->createOrderPaymentAction->expects($this->once())->method('execute')->with($response);
        $this->setCompletedOrderStateAction->expects($this->once())->method('execute')->with('paypal-order-123');

        $this->handler->handle($response);
    }

    public function testHandleWhenOtherPsCheckoutExceptionPropagates(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart not found');

        $response = $this->createMock(PayPalOrderResponse::class);

        $this->createOrderAction
            ->method('execute')
            ->willThrowException(new PsCheckoutException('Cart not found', PsCheckoutException::PRESTASHOP_CART_NOT_FOUND));

        // Must not reach payment or state steps when a non-duplicate exception fires.
        $this->createOrderPaymentAction->expects($this->never())->method('execute');
        $this->setCompletedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($response);
    }

    public function testHandleWhenUnexpectedExceptionPropagates(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unexpected failure');

        $response = $this->createMock(PayPalOrderResponse::class);

        $this->createOrderAction
            ->method('execute')
            ->willThrowException(new \RuntimeException('Unexpected failure'));

        $this->createOrderPaymentAction->expects($this->never())->method('execute');
        $this->setCompletedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($response);
    }
}
