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

namespace Tests\Unit\PsCheckout\Infrastructure\Action;

use Cart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Action\ShippingCallbackProcessor;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use Psr\Log\LoggerInterface;

class ShippingCallbackProcessorTest extends TestCase
{
    /** @var CartInterface|MockObject */
    private $cartAdapter;

    /** @var ShippingOptionsBuilderInterface|MockObject */
    private $shippingOptionsBuilder;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ShippingCallbackProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->cartAdapter = $this->createMock(CartInterface::class);
        $this->shippingOptionsBuilder = $this->createMock(ShippingOptionsBuilderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->processor = new ShippingCallbackProcessor($this->cartAdapter, $this->shippingOptionsBuilder, $this->logger);
    }

    public function testThrowsMethodUnavailableWhenCartNotFound(): void
    {
        $this->cartAdapter->method('getCart')->willReturn(null);

        try {
            $this->processor->process(99, new ShippingCallbackPayload([]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::METHOD_UNAVAILABLE, $e->getIssue());
        }
    }

    public function testThrowsAddressErrorWhenNoDeliveryOptions(): void
    {
        $cart = $this->makeCart([]);

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->logger->expects($this->once())->method('warning');

        try {
            $this->processor->process(1, new ShippingCallbackPayload([]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::ADDRESS_ERROR, $e->getIssue());
        }
    }

    public function testResponseContainsExpectedStructure(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);

        $shippingOptions = [
            ['id' => 'delivery-option-3', 'label' => 'La Poste', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '4.99'], 'selected' => true],
        ];
        $this->shippingOptionsBuilder->method('build')->willReturn($shippingOptions);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);

        $result = $this->processor->process(1, new ShippingCallbackPayload(['id' => 'ORDER-1']));

        $this->assertArrayHasKey('purchase_units', $result);
        $unit = $result['purchase_units'][0];

        $this->assertSame('default', $unit['reference_id']);
        $this->assertArrayHasKey('amount', $unit);
        $this->assertArrayHasKey('value', $unit['amount']);
        $this->assertArrayHasKey('breakdown', $unit['amount']);
        $this->assertArrayHasKey('item_total', $unit['amount']['breakdown']);
        $this->assertArrayHasKey('tax_total', $unit['amount']['breakdown']);
        $this->assertArrayHasKey('shipping', $unit['amount']['breakdown']);
        $this->assertArrayHasKey('shipping_options', $unit);
        $this->assertSame($shippingOptions, $unit['shipping_options']);
    }

    public function testBuilderReceivesPayloadShippingOptionId(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-5', 'label' => 'DHL', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '7.00'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(7.00);

        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_option' => ['id' => 'delivery-option-5'],
        ]);

        $this->shippingOptionsBuilder
            ->expects($this->once())
            ->method('build')
            ->with(1, 'delivery-option-5');

        $this->processor->process(1, $payload);
    }

    /**
     * @param array<string, mixed> $deliveryOptions non-empty to simulate available options
     *
     * @return Cart|MockObject
     */
    private function makeCart(array $deliveryOptions): Cart
    {
        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cart->id = 1;
        $cart->id_currency = 0;

        $cart->method('getDeliveryOptionList')->willReturn($deliveryOptions);
        $cart->method('getDeliveryOption')->willReturn([1 => '3,']);
        $cart->method('getOrderTotal')->willReturn(100.00);

        return $cart;
    }
}
