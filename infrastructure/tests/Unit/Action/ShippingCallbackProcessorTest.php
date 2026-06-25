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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\PurchaseUnitsNodeBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Action\ShippingCallbackProcessor;
use PsCheckout\Infrastructure\Adapter\CartDataInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Service\CountryResolutionException;
use PsCheckout\Infrastructure\Service\PaypalAddressResolverInterface;
use Psr\Log\LoggerInterface;

class ShippingCallbackProcessorTest extends TestCase
{
    /** @var CartInterface|MockObject */
    private $cartAdapter;

    /** @var ShippingOptionsBuilderInterface|MockObject */
    private $shippingOptionsBuilder;

    /** @var PurchaseUnitsNodeBuilderInterface|MockObject */
    private $purchaseUnitsNodeBuilder;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var PaypalAddressResolverInterface|MockObject */
    private $addressResolver;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ShippingCallbackProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->cartAdapter = $this->createMock(CartInterface::class);
        $this->shippingOptionsBuilder = $this->createMock(ShippingOptionsBuilderInterface::class);
        $this->purchaseUnitsNodeBuilder = $this->createMock(PurchaseUnitsNodeBuilderInterface::class);
        $shop = new \stdClass();
        $shop->id = 1;
        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('getShop')->willReturn($shop);
        $this->addressResolver = $this->createMock(PaypalAddressResolverInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->processor = new ShippingCallbackProcessor(
            $this->cartAdapter,
            $this->shippingOptionsBuilder,
            $this->purchaseUnitsNodeBuilder,
            $this->context,
            $this->addressResolver,
            $this->createMock(AddressRepositoryInterface::class),
            $this->logger
        );
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

    public function testDelegatesToPurchaseUnitsNodeBuilderWithCorrectAmounts(): void
    {
        $cart = $this->makeCart(['placeholder' => true], 80.00, 90.00);
        $this->cartAdapter->method('getCart')->willReturn($cart);

        $shippingOptions = [
            ['id' => 'delivery-option-3', 'label' => 'La Poste', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '4.99'], 'selected' => true],
        ];
        $this->shippingOptionsBuilder->method('build')->willReturn($shippingOptions);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);

        $builtUnits = ['purchase_units' => [['reference_id' => 'default']]];

        // item=80.00, tax=90.00-80.00=10.00, shipping=4.99, reference_id from payload
        $this->purchaseUnitsNodeBuilder
            ->expects($this->once())
            ->method('build')
            ->with('default', $this->anything(), 80.00, 10.00, 4.99, $shippingOptions)
            ->willReturn($builtUnits);

        $result = $this->processor->process(1, new ShippingCallbackPayload(['id' => 'ORDER-1']));

        $this->assertSame('ORDER-1', $result['id']);
        $this->assertSame($builtUnits['purchase_units'], $result['purchase_units']);
    }

    public function testUpdatesCartDeliveryOptionOnShippingOptionEvent(): void
    {
        $cart = $this->createMock(CartDataInterface::class);
        $cart->method('getId')->willReturn(1);
        $cart->method('getCurrencyIsoCode')->willReturn('EUR');
        $cart->method('getDeliveryAddressId')->willReturn(5);
        $cart->method('getDeliveryOptionList')->willReturn([5 => ['carrier_list' => ['3' => []]]]);
        $cart->method('getDeliveryOption')->willReturn([5 => '3,']);
        $cart->method('getProductsTotalWithoutTax')->willReturn(100.00);
        $cart->method('getProductsTotalWithTax')->willReturn(100.00);
        $cart->expects($this->once())->method('setDeliveryOption')->with(5, 3);
        $cart->expects($this->once())->method('save');

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);

        $this->processor->process(1, new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_option' => ['id' => 'delivery-option-3'],
        ]));
    }

    public function testUsesEffectiveAddressFromOptionListWhenDeliveryAddressNotInList(): void
    {
        // Simulates the bug where ps_cart_product rows keep the original customer address (44)
        // even though id_address_delivery was updated to the temp address (47). The delivery option
        // list is therefore keyed by 44, not 47. The processor must use 44 so setDeliveryOption
        // passes Cart's internal validation and does not silently discard the carrier selection.
        $cart = $this->createMock(CartDataInterface::class);
        $cart->method('getId')->willReturn(1);
        $cart->method('getCurrencyIsoCode')->willReturn('EUR');
        $cart->method('getDeliveryAddressId')->willReturn(47);
        $cart->method('getDeliveryOptionList')->willReturn([44 => ['carrier_list' => ['2' => []]]]);
        $cart->method('getDeliveryOption')->willReturn([44 => '1,']);
        $cart->method('getProductsTotalWithoutTax')->willReturn(100.00);
        $cart->method('getProductsTotalWithTax')->willReturn(100.00);
        $cart->expects($this->once())->method('setDeliveryOption')->with(44, 2);
        $cart->expects($this->once())->method('save');

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-2', 'amount' => ['value' => '9.67'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(9.67);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);

        $this->processor->process(1, new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_option' => ['id' => 'delivery-option-2'],
        ]));
    }

    public function testLogsWarningWhenDeliveryAddressIdIsZeroOnShippingOptionEvent(): void
    {
        $cart = $this->makeCart(['placeholder' => true]); // getDeliveryAddressId returns 0
        $cart->expects($this->never())->method('setDeliveryOption');

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);
        $this->logger->expects($this->once())->method('warning');

        $this->processor->process(1, new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_option' => ['id' => 'delivery-option-3'],
        ]));
    }

    public function testDoesNotUpdateCartDeliveryOptionOnAddressEvent(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $cart->expects($this->never())->method('setDeliveryOption');

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);

        $this->processor->process(1, new ShippingCallbackPayload(['id' => 'ORDER-1']));
    }

    public function testThrowsAddressErrorWhenAllCarriersDisabled(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([]);
        $this->logger->expects($this->once())->method('warning');

        try {
            $this->processor->process(1, new ShippingCallbackPayload(['id' => 'ORDER-1']));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::ADDRESS_ERROR, $e->getIssue());
        }
    }

    public function testThrowsMethodUnavailableWhenShippingOptionIdNotInAvailableOptions(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);

        try {
            $this->processor->process(1, new ShippingCallbackPayload([
                'id' => 'ORDER-1',
                'shipping_option' => ['id' => 'delivery-option-99'],
            ]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::METHOD_UNAVAILABLE, $e->getIssue());
        }
    }

    public function testThrowsMethodUnavailableWhenShippingOptionIdHasUnrecognisedFormat(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('build')->willReturn([
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => true],
        ]);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(4.99);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);

        try {
            $this->processor->process(1, new ShippingCallbackPayload([
                'id' => 'ORDER-1',
                'shipping_option' => ['id' => 'carrier-42'],
            ]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::METHOD_UNAVAILABLE, $e->getIssue());
        }
    }

    public function testBuilderReceivesPayloadShippingOptionId(): void
    {
        $cart = $this->makeCart(['placeholder' => true]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->shippingOptionsBuilder->method('getSelectedShippingPrice')->willReturn(7.00);
        $this->purchaseUnitsNodeBuilder->method('build')->willReturn(['purchase_units' => []]);

        $shippingOptions = [
            ['id' => 'delivery-option-5', 'label' => 'DHL', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '7.00'], 'selected' => true],
        ];

        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_option' => ['id' => 'delivery-option-5'],
        ]);

        $this->shippingOptionsBuilder
            ->expects($this->once())
            ->method('build')
            ->with(1, 'delivery-option-5', $this->anything())
            ->willReturn($shippingOptions);

        $this->processor->process(1, $payload);
    }

    public function testThrowsCountryErrorWhenCountryNotFound(): void
    {
        $cart = $this->makeCart([]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->addressResolver->method('resolveCountryState')->willThrowException(
            new CountryResolutionException('Country not found', CountryResolutionException::COUNTRY_NOT_FOUND, 'ZZ')
        );

        try {
            $this->processor->process(1, new ShippingCallbackPayload([
                'id' => 'ORDER-1',
                'shipping_address' => ['country_code' => 'ZZ'],
            ]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::COUNTRY_ERROR, $e->getIssue());
        }
    }

    public function testThrowsCountryErrorWhenCountryNotAvailableForDelivery(): void
    {
        $cart = $this->makeCart([]);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->addressResolver->method('resolveCountryState')->willThrowException(
            new CountryResolutionException('Country not available', CountryResolutionException::COUNTRY_NOT_AVAILABLE, 'US', 5)
        );

        try {
            $this->processor->process(1, new ShippingCallbackPayload([
                'id' => 'ORDER-1',
                'shipping_address' => ['country_code' => 'US'],
            ]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::COUNTRY_ERROR, $e->getIssue());
        }
    }

    /**
     * @param array<string, mixed> $deliveryOptions non-empty to simulate available options
     *
     * @return CartDataInterface|MockObject
     */
    private function makeCart(array $deliveryOptions, float $itemsWithoutTax = 100.00, float $itemsWithTax = 100.00): CartDataInterface
    {
        $cart = $this->createMock(CartDataInterface::class);

        $cart->method('getId')->willReturn(1);
        $cart->method('getCurrencyIsoCode')->willReturn('EUR');
        $cart->method('getDeliveryAddressId')->willReturn(0);
        $cart->method('getDeliveryOptionList')->willReturn($deliveryOptions);
        $cart->method('getDeliveryOption')->willReturn([1 => '3,']);
        $cart->method('getProductsTotalWithoutTax')->willReturn($itemsWithoutTax);
        $cart->method('getProductsTotalWithTax')->willReturn($itemsWithTax);

        return $cart;
    }
}
