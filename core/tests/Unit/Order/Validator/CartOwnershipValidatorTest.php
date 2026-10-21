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

namespace PsCheckout\Tests\Unit\Order\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Validator\CartOwnershipValidator;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class CartOwnershipValidatorTest extends TestCase
{
    /** @var CartOwnershipValidator */
    private $validator;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var CartInterface|MockObject */
    private $cartAdapter;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->cartAdapter = $this->createMock(CartInterface::class);

        $this->validator = new CartOwnershipValidator($this->context, $this->cartAdapter);
    }

    public function testItReturnsTrueWhenCartBelongsToCurrentCustomer(): void
    {
        $this->context->method('getCustomer')->willReturn($this->createCustomer(42));
        $this->cartAdapter->method('getCart')->with(10)->willReturn($this->createCart(42));

        $this->assertTrue($this->validator->isOwnedByCurrentCustomer(10));
    }

    public function testItReturnsFalseWhenCartBelongsToAnotherCustomer(): void
    {
        $this->context->method('getCustomer')->willReturn($this->createCustomer(42));
        $this->cartAdapter->method('getCart')->with(10)->willReturn($this->createCart(7));

        $this->assertFalse($this->validator->isOwnedByCurrentCustomer(10));
    }

    public function testItReturnsFalseWhenCartIsNotFound(): void
    {
        $this->context->method('getCustomer')->willReturn($this->createCustomer(42));
        $this->cartAdapter->method('getCart')->willReturn(null);

        $this->assertFalse($this->validator->isOwnedByCurrentCustomer(10));
    }

    public function testItReturnsFalseWhenThereIsNoCustomerInContext(): void
    {
        $this->context->method('getCustomer')->willReturn(null);
        $this->cartAdapter->expects($this->never())->method('getCart');

        $this->assertFalse($this->validator->isOwnedByCurrentCustomer(10));
    }

    public function testItReturnsFalseForAnonymousVisitorEvenWhenCartHasNoCustomer(): void
    {
        $this->context->method('getCustomer')->willReturn($this->createCustomer(0));
        $this->cartAdapter->method('getCart')->willReturn($this->createCart(0));

        $this->assertFalse($this->validator->isOwnedByCurrentCustomer(10));
    }

    /**
     * @param int $customerId
     *
     * @return \Customer|MockObject
     */
    private function createCustomer(int $customerId)
    {
        $customer = $this->getMockBuilder(\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->id = $customerId;

        return $customer;
    }

    /**
     * @param int $customerId
     *
     * @return \Cart|MockObject
     */
    private function createCart(int $customerId)
    {
        $cart = $this->getMockBuilder(\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id = 10;
        $cart->id_customer = $customerId;

        return $cart;
    }
}
