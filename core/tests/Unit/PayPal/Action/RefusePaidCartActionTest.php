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

namespace PsCheckout\Core\Tests\Unit\PayPal\Action;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Action\RefusePaidCartAction;
use PsCheckout\Core\PayPal\Order\Exception\CartAlreadyPaidException;
use PsCheckout\Core\PayPal\Order\Validator\PaidCartValidatorInterface;
use Psr\Log\LoggerInterface;

class RefusePaidCartActionTest extends TestCase
{
    /** @var PaidCartValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paidCartValidator;

    /** @var CreateOrderActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $createOrderAction;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var RefusePaidCartAction */
    private $action;

    protected function setUp(): void
    {
        $this->paidCartValidator = $this->createMock(PaidCartValidatorInterface::class);
        $this->createOrderAction = $this->createMock(CreateOrderActionInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->action = new RefusePaidCartAction($this->paidCartValidator, $this->createOrderAction, $this->logger);
    }

    public function testDoesNothingWhenTheCartIsNotPaid(): void
    {
        $this->paidCartValidator->expects($this->once())->method('validate')->with(42);
        $this->createOrderAction->expects($this->never())->method('execute');

        $this->action->execute(42);
    }

    public function testCreatesTheOrderOfThePaidPayPalOrderThenRefuses(): void
    {
        $paidPayPalOrder = $this->createMock(PayPalOrderResponse::class);
        $refusal = new CartAlreadyPaidException('Previous PayPal order of the cart is already paid', $paidPayPalOrder);
        $this->paidCartValidator->method('validate')->willThrowException($refusal);

        $this->createOrderAction->expects($this->once())->method('execute')->with($paidPayPalOrder);

        $this->assertSame($refusal, $this->executeAndCatch());
    }

    public function testRefusesWithoutCreatingAnOrderWhenThePaidPayPalOrderWasNotRead(): void
    {
        $refusal = new CartAlreadyPaidException('Cart already has an order');
        $this->paidCartValidator->method('validate')->willThrowException($refusal);

        $this->createOrderAction->expects($this->never())->method('execute');

        $this->assertSame($refusal, $this->executeAndCatch());
    }

    public function testStillRefusesAndLogsWhenTheOrderCannotBeCreated(): void
    {
        $paidPayPalOrder = $this->createMock(PayPalOrderResponse::class);
        $paidPayPalOrder->method('getId')->willReturn('ORDER-A');
        $refusal = new CartAlreadyPaidException('Previous PayPal order of the cart is already paid', $paidPayPalOrder);
        $this->paidCartValidator->method('validate')->willThrowException($refusal);
        $this->createOrderAction->method('execute')->willThrowException(
            new PsCheckoutException('Order cannot be saved', PsCheckoutException::PRESTASHOP_VALIDATE_ORDER)
        );

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->anything(),
                $this->callback(function (array $context): bool {
                    return $context['cart_id'] === 42 && $context['paypal_order_id'] === 'ORDER-A';
                })
            );

        $this->assertSame($refusal, $this->executeAndCatch());
    }

    public function testDoesNotLogAnErrorWhenTheOrderWasCreatedInTheMeantime(): void
    {
        $refusal = new CartAlreadyPaidException('Previous PayPal order of the cart is already paid', $this->createMock(PayPalOrderResponse::class));
        $this->paidCartValidator->method('validate')->willThrowException($refusal);
        $this->createOrderAction->method('execute')->willThrowException(
            new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS)
        );

        $this->logger->expects($this->never())->method('error');

        $this->assertSame($refusal, $this->executeAndCatch());
    }

    private function executeAndCatch(): CartAlreadyPaidException
    {
        try {
            $this->action->execute(42);
        } catch (CartAlreadyPaidException $exception) {
            return $exception;
        }

        $this->fail('The paid cart was not refused');
    }
}
