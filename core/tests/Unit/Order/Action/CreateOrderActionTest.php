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
use PsCheckout\Core\Order\Action\CreateOrderAction;
use PsCheckout\Core\Order\Action\CreateValidateOrderDataActionInterface;
use PsCheckout\Core\Order\Action\ValidateOrderActionInterface;
use PsCheckout\Core\Order\Validator\CheckoutValidatorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderMatrixRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;

class CreateOrderActionTest extends TestCase
{
    private $context;

    private $createValidateOrderDataAction;

    private $validateOrderAction;

    private $orderRepository;

    private $orderMatrixRepository;

    private $checkoutValidator;

    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->createValidateOrderDataAction = $this->createMock(CreateValidateOrderDataActionInterface::class);
        $this->validateOrderAction = $this->createMock(ValidateOrderActionInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->orderMatrixRepository = $this->createMock(PayPalOrderMatrixRepositoryInterface::class);
        $this->checkoutValidator = $this->createMock(CheckoutValidatorInterface::class);

        $this->action = new CreateOrderAction(
            $this->context,
            $this->createValidateOrderDataAction,
            $this->validateOrderAction,
            $this->orderRepository,
            $this->orderMatrixRepository,
            $this->checkoutValidator
        );
    }

    public function testExecuteThrowsWhenOrderAlreadyExistsForCart(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS);

        $request = $this->createMock(PayPalOrderResponse::class);
        $request->method('getId')->willReturn('PAYPAL-ORDER-123');

        $cart = $this->createMock(\Cart::class);
        $cart->id = 42;
        $this->context->method('getCart')->willReturn($cart);

        $this->checkoutValidator
            ->method('validate')
            ->willThrowException(
                new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS)
            );

        // createValidateOrderDataAction must never be reached when validator throws
        $this->createValidateOrderDataAction->expects($this->never())->method('execute');

        $this->action->execute($request);
    }
}
