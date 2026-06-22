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

namespace PsCheckout\Core\Tests\Unit\Customer\Action;

use Cart;
use Customer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Customer\Action\ExpressCheckoutAction;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutPayerData;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Action\CreateOrUpdateAddressActionInterface;
use PsCheckout\Infrastructure\Action\CustomerAuthenticationActionInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

if (!defined('_COOKIE_KEY_')) {
    define('_COOKIE_KEY_', 'test_cookie_key');
}

class ExpressCheckoutActionTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    private $context;

    /** @var CustomerAuthenticationActionInterface|MockObject */
    private $customerAuthenticationAction;

    /** @var CreateOrUpdateAddressActionInterface|MockObject */
    private $createOrUpdateAddressAction;

    /** @var ExpressCheckoutAction */
    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->customerAuthenticationAction = $this->createMock(CustomerAuthenticationActionInterface::class);
        $this->createOrUpdateAddressAction = $this->createMock(CreateOrUpdateAddressActionInterface::class);

        $this->action = new ExpressCheckoutAction(
            $this->context,
            $this->customerAuthenticationAction,
            $this->createOrUpdateAddressAction
        );
    }

    private function makePayerData(
        string $orderId = 'ORDER-1',
        string $email = 'john@example.com',
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $phone = '5551234567',
        ?string $birthDate = null
    ): ExpressCheckoutPayerData {
        return new ExpressCheckoutPayerData($orderId, $email, $firstName, $lastName, $phone, $birthDate);
    }

    private function makeShippingData(
        string $orderId = 'ORDER-1',
        string $countryCode = 'US'
    ): ExpressCheckoutShippingData {
        return new ExpressCheckoutShippingData(
            $orderId,
            'John',
            'Doe',
            '123 Main St',
            '',
            '10001',
            'New York',
            'NY',
            $countryCode,
            '5551234567'
        );
    }

    public function testCreatesGuestCustomerWhenNotLoggedIn(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(false);
        $customer->expects($this->once())->method('save');

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->expects($this->once())->method('updateCustomer')->with($customer);
        $this->context->method('getCart')->willReturn(null);

        $payerData = $this->makePayerData();

        $this->action->execute($payerData, $this->makeShippingData());

        $this->assertTrue($customer->is_guest);
        $this->assertSame('john@example.com', $customer->email);
        $this->assertSame('John', $customer->firstname);
        $this->assertSame('Doe', $customer->lastname);
    }

    public function testSkipsCustomerCreationWhenAlreadyLoggedIn(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);
        $customer->expects($this->never())->method('save');

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->expects($this->never())->method('updateCustomer');
        $this->context->method('getCart')->willReturn(null);

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }

    public function testSetsPayPalEmailOnContext(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn(null);
        $this->context->expects($this->once())
            ->method('setPayPalEmail')
            ->with('john@example.com');

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }

    public function testCreatesAddressWhenCartHasNoDeliveryAddress(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_address_delivery = 0;

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn($cart);

        $shippingData = $this->makeShippingData();

        $this->createOrUpdateAddressAction->expects($this->once())
            ->method('execute')
            ->with($shippingData);

        $this->action->execute($this->makePayerData(), $shippingData);
    }

    public function testAlwaysCreatesOrUpdatesAddressEvenWhenCartAlreadyHasDeliveryAddress(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_address_delivery = 5;

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn($cart);

        $this->createOrUpdateAddressAction->expects($this->once())
            ->method('execute');

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }

    public function testSkipsAddressCreationWhenCartIsNull(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn(null);

        $this->createOrUpdateAddressAction->expects($this->once())
            ->method('execute');

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }

    public function testRestoresCartDeliveryStateAfterGuestCustomerAuthentication(): void
    {
        // PS Context::updateCustomer resets id_address_delivery to the customer's first address
        // (0 for a brand-new guest) and clears/corrupts delivery_option. The action must restore
        // both so the carrier selected via the shipping callback is not lost.
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(false);
        $customer->method('save')->willReturn(true);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_address_delivery = 50;
        $cart->delivery_option = '{"50":"2,"}';
        $cart->expects($this->once())->method('save');

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn($cart);
        $this->context->method('updateCustomer')->willReturnCallback(function () use ($cart) {
            $cart->id_address_delivery = 0;
            $cart->delivery_option = '["2,"]';
        });

        $this->action->execute($this->makePayerData(), $this->makeShippingData());

        $this->assertSame(50, $cart->id_address_delivery);
        $this->assertSame('{"50":"2,"}', $cart->delivery_option);
    }

    public function testWrapsCustomerSaveExceptionInPsCheckoutException(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(false);
        $customer->method('save')->willThrowException(new \Exception('DB error'));

        $this->context->method('getCustomer')->willReturn($customer);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_CUSTOMER);

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }

    public function testNullDeliveryAddressIdTriggersAddressCreation(): void
    {
        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->method('isLogged')->willReturn(true);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id_address_delivery = null;

        $this->context->method('getCustomer')->willReturn($customer);
        $this->context->method('getCart')->willReturn($cart);

        $this->createOrUpdateAddressAction->expects($this->once())
            ->method('execute');

        $this->action->execute($this->makePayerData(), $this->makeShippingData());
    }
}
