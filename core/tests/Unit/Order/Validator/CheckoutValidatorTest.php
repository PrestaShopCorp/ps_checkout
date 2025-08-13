<?php

namespace PsCheckout\Tests\Unit\Order\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Validator\CheckoutValidator;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class CheckoutValidatorTest extends TestCase
{
    /** @var CheckoutValidator */
    private $validator;

    /** @var PayPalOrderRepository|MockObject */
    private $payPalOrderRepository;

    /** @var OrderRepositoryInterface|MockObject */
    private $orderRepository;

    /** @var CartRepositoryInterface|MockObject */
    private $cartRepository;

    protected function setUp(): void
    {
        $this->payPalOrderRepository = $this->getMockBuilder(PayPalOrderRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOneBy'])
            ->getMock();

        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getAllBy'])
            ->getMock();

        $this->cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOneBy'])
            ->getMock();

        $this->validator = new CheckoutValidator(
            $this->payPalOrderRepository,
            $this->orderRepository,
            $this->cartRepository
        );
    }

    public function testItThrowsExceptionWhenPayPalOrderNotFound(): void
    {
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order not found');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);

        $this->validator->validate('ORDER-123', 1);
    }

    public function testItThrowsExceptionWhenCartNotFound(): void
    {
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(['id' => 'ORDER-123']);

        $this->cartRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => 1])
            ->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart does not exist');
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);

        $this->validator->validate('ORDER-123', 1);
    }

    public function testItThrowsExceptionWhenCartHasNoProducts(): void
    {
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(['id' => 'ORDER-123']);

        $cart = $this->createCartMock(false);
        $this->cartRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => 1])
            ->willReturn($cart);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart with id 1 has no product. Cannot create the order.');
        $this->expectExceptionCode(PsCheckoutException::CART_PRODUCT_MISSING);

        $this->validator->validate('ORDER-123', 1);
    }

    public function testItThrowsExceptionWhenOrderAlreadyExists(): void
    {
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(['id' => 'ORDER-123']);

        $cart = $this->createCartMock(true);
        $this->cartRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => 1])
            ->willReturn($cart);

        $this->orderRepository->expects($this->once())
            ->method('getAllBy')
            ->with(['id_cart' => 1])
            ->willReturn([['id_order' => 1]]);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Order already exist');
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS);

        $this->validator->validate('ORDER-123', 1);
    }

    public function testItValidatesSuccessfully(): void
    {
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(['id' => 'ORDER-123']);

        $cart = $this->createCartMock(true);
        $this->cartRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => 1])
            ->willReturn($cart);

        $this->orderRepository->expects($this->once())
            ->method('getAllBy')
            ->with(['id_cart' => 1])
            ->willReturn([]);

        $this->validator->validate('ORDER-123', 1);
        $this->assertTrue(true, 'Validation passed successfully');
    }

    private function createCartMock(bool $valid = true): MockObject
    {
        $cart = $this->getMockBuilder(\Cart::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProducts'])
            ->getMock();

        $cart->id = 1;
        $cart->method('getProducts')
            ->with(true)
            ->willReturn($valid ? [['id_product' => 1]] : []);

        return $cart;
    }
}
