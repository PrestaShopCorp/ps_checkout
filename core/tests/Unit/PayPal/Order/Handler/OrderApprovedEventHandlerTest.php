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
use PsCheckout\Core\Order\Validator\OrderAuthorizationValidatorInterface;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Handler\OrderApprovedEventHandler;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\OrderStatus\Action\PayPalCheckOrderStatusActionInterface;

class OrderApprovedEventHandlerTest extends TestCase
{
    /** @var PayPalOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderRepository;

    /** @var PayPalCheckOrderStatusActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $checkPayPalOrderStatusAction;

    /** @var OrderAuthorizationValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderAuthorizationValidator;

    /** @var CapturePayPalOrderActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $capturePayPalOrderAction;

    /** @var UpdatePayPalOrderPurchaseUnitActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $updatePayPalOrderPurchaseUnit;

    /** @var OrderApprovedEventHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->checkPayPalOrderStatusAction = $this->createMock(PayPalCheckOrderStatusActionInterface::class);
        $this->orderAuthorizationValidator = $this->createMock(OrderAuthorizationValidatorInterface::class);
        $this->capturePayPalOrderAction = $this->createMock(CapturePayPalOrderActionInterface::class);
        $this->updatePayPalOrderPurchaseUnit = $this->createMock(UpdatePayPalOrderPurchaseUnitActionInterface::class);

        $this->handler = new OrderApprovedEventHandler(
            $this->payPalOrderRepository,
            $this->checkPayPalOrderStatusAction,
            $this->orderAuthorizationValidator,
            $this->capturePayPalOrderAction,
            $this->updatePayPalOrderPurchaseUnit
        );
    }

    public function testHandleThrowsWhenOrderNotFound(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::ORDER_NOT_FOUND);

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    public function testHandleReturnsSilentlyWhenStatusTransitionInvalid(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn($this->createPayPalOrderMock());
        $this->checkPayPalOrderStatusAction->method('execute')->willReturn(false);

        $this->capturePayPalOrderAction->expects($this->never())->method('execute');

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    public function testHandleReturnsSilentlyWhenExpressCheckout(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn($this->createPayPalOrderMock('CREATED', true));
        $this->checkPayPalOrderStatusAction->method('execute')->willReturn(true);

        $this->orderAuthorizationValidator->expects($this->never())->method('validate');
        $this->capturePayPalOrderAction->expects($this->never())->method('execute');

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    public function testHandleReturnsSilentlyWhenOrderDeleted(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn($this->createPayPalOrderMock('CREATED', false, true));
        $this->checkPayPalOrderStatusAction->method('execute')->willReturn(true);

        $this->orderAuthorizationValidator->expects($this->never())->method('validate');
        $this->capturePayPalOrderAction->expects($this->never())->method('execute');

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    public function testHandleDoesNotCaptureWhenValidatorRefuses(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn($this->createPayPalOrderMock());
        $this->checkPayPalOrderStatusAction->method('execute')->willReturn(true);
        $this->orderAuthorizationValidator->method('validate')->willThrowException(
            new PsCheckoutException('Cart invoice address is incorrect.', PsCheckoutException::CART_ADDRESS_INVOICE_INVALID)
        );

        $this->capturePayPalOrderAction->expects($this->never())->method('execute');

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    public function testHandleCapturesWhenValidatorPasses(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn($this->createPayPalOrderMock());
        $this->checkPayPalOrderStatusAction->method('execute')->willReturn(true);

        $this->capturePayPalOrderAction->expects($this->once())->method('execute');

        $this->handler->handle($this->createPayPalOrderResponseMock());
    }

    /**
     * @param string $status
     * @param bool $isExpressCheckout
     * @param bool $isDeleted
     * @param int $cartId
     *
     * @return PayPalOrder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderMock(
        string $status = 'CREATED',
        bool $isExpressCheckout = false,
        bool $isDeleted = false,
        int $cartId = 42
    ) {
        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getStatus')->willReturn($status);
        $payPalOrder->method('getIdCart')->willReturn($cartId);
        $payPalOrder->method('isExpressCheckout')->willReturn($isExpressCheckout);
        $payPalOrder->method('hasTag')->willReturn($isDeleted);

        return $payPalOrder;
    }

    /**
     * @return PayPalOrderResponse|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderResponseMock(string $status = 'APPROVED')
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('ORDER-123');
        $response->method('getStatus')->willReturn($status);

        return $response;
    }
}
