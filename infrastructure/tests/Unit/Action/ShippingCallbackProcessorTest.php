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
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Action\ShippingCallbackProcessor;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutCarrierRepository;
use Psr\Log\LoggerInterface;

class ShippingCallbackProcessorTest extends TestCase
{
    /** @var CartInterface|MockObject */
    private $cartAdapter;

    /** @var PsCheckoutCarrierRepository|MockObject */
    private $carrierRepository;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ShippingCallbackProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->cartAdapter = $this->createMock(CartInterface::class);
        $this->carrierRepository = $this->createMock(PsCheckoutCarrierRepository::class);
        $this->carrierRepository->method('getTypeByCarrierId')->willReturn('SHIPPING');
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->processor = new ShippingCallbackProcessor($this->cartAdapter, $this->carrierRepository, $this->logger);
    }

    // -------------------------------------------------------------------------
    // formatShippingOptionIdFromCarrierId
    // -------------------------------------------------------------------------

    public function testFormatShippingOptionIdFromCarrierId(): void
    {
        $this->assertSame('delivery-option-3', $this->processor->formatShippingOptionIdFromCarrierId('3'));
        $this->assertSame('delivery-option-42', $this->processor->formatShippingOptionIdFromCarrierId('42'));
        $this->assertSame('delivery-option-0', $this->processor->formatShippingOptionIdFromCarrierId('0'));
    }

    // -------------------------------------------------------------------------
    // Guard — cart not found
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Guard — no delivery options
    // -------------------------------------------------------------------------

    public function testThrowsAddressErrorWhenNoDeliveryOptions(): void
    {
        $cart = $this->makeCart([], []);

        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->logger->expects($this->once())->method('warning');

        try {
            $this->processor->process(1, new ShippingCallbackPayload([]));
            $this->fail('Expected ShippingCallbackException');
        } catch (ShippingCallbackException $e) {
            $this->assertSame(ShippingCallbackException::ADDRESS_ERROR, $e->getIssue());
        }
    }

    // -------------------------------------------------------------------------
    // Selection priority: PS cart's selected carrier (SHIPPING_ADDRESS event)
    // -------------------------------------------------------------------------

    public function testPsSelectedCarrierIsMarkedSelectedOnAddressEvent(): void
    {
        // Cart has carriers 3 and 5; carrier 3 is the PS-selected one
        $carriers = [
            3 => ['name' => 'La Poste', 'price' => 4.99],
            5 => ['name' => 'DHL', 'price' => 7.00],
        ];
        $cart = $this->makeCart($carriers, [1 => '3,']);

        $this->cartAdapter->method('getCart')->willReturn($cart);

        // Payload has no shippingOptionId → SHIPPING_ADDRESS event
        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_address' => ['country_code' => 'FR', 'admin_area_1' => '', 'admin_area_2' => 'Paris', 'postal_code' => '75001'],
        ]);

        $result = $this->processor->process(1, $payload);

        $options = $result['purchase_units'][0]['shipping_options'];
        $this->assertCount(2, $options);

        $selectedOption = $this->findOptionById($options, 'delivery-option-3');
        $this->assertNotNull($selectedOption, 'delivery-option-3 should exist');
        $this->assertTrue($selectedOption['selected']);

        $unselectedOption = $this->findOptionById($options, 'delivery-option-5');
        $this->assertNotNull($unselectedOption, 'delivery-option-5 should exist');
        $this->assertFalse($unselectedOption['selected']);
    }

    // -------------------------------------------------------------------------
    // Selection priority: PayPal payload option ID takes absolute priority
    // -------------------------------------------------------------------------

    public function testPayloadOptionIdTakesPriorityOverPsSelection(): void
    {
        // Cart has carriers 3 and 5; PS has carrier 3 selected
        $carriers = [
            3 => ['name' => 'La Poste', 'price' => 4.99],
            5 => ['name' => 'DHL', 'price' => 7.00],
        ];
        $cart = $this->makeCart($carriers, [1 => '3,']);

        $this->cartAdapter->method('getCart')->willReturn($cart);

        // Payload explicitly selects delivery-option-5 (SHIPPING_OPTIONS event)
        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_address' => ['country_code' => 'FR', 'admin_area_1' => '', 'admin_area_2' => 'Paris', 'postal_code' => '75001'],
            'shipping_option' => ['id' => 'delivery-option-5'],
        ]);

        $result = $this->processor->process(1, $payload);

        $options = $result['purchase_units'][0]['shipping_options'];

        $this->assertFalse($this->findOptionById($options, 'delivery-option-3')['selected']);
        $this->assertTrue($this->findOptionById($options, 'delivery-option-5')['selected']);
    }

    // -------------------------------------------------------------------------
    // Fallback to first option when neither PS nor payload matches
    // -------------------------------------------------------------------------

    public function testFallsBackToFirstOptionWhenNoPsSelectionAndNoPayloadOption(): void
    {
        // Cart has carriers 3 and 5; PS returns 0 (no selection yet)
        $carriers = [
            3 => ['name' => 'La Poste', 'price' => 4.99],
            5 => ['name' => 'DHL', 'price' => 7.00],
        ];
        $cart = $this->makeCart($carriers, [1 => '0,']);

        $this->cartAdapter->method('getCart')->willReturn($cart);

        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_address' => ['country_code' => 'FR', 'admin_area_1' => '', 'admin_area_2' => 'Paris', 'postal_code' => '75001'],
        ]);

        $result = $this->processor->process(1, $payload);

        $options = $result['purchase_units'][0]['shipping_options'];

        // First option (carrier 3) should be selected as fallback
        $this->assertTrue($options[0]['selected']);
        $this->assertFalse($options[1]['selected']);
    }

    // -------------------------------------------------------------------------
    // Response structure
    // -------------------------------------------------------------------------

    public function testResponseContainsExpectedStructure(): void
    {
        $carriers = [3 => ['name' => 'La Poste', 'price' => 4.99]];
        $cart = $this->makeCart($carriers, [1 => '3,']);

        $this->cartAdapter->method('getCart')->willReturn($cart);

        $payload = new ShippingCallbackPayload([
            'id' => 'ORDER-1',
            'shipping_address' => ['country_code' => 'FR', 'admin_area_1' => '', 'admin_area_2' => 'Paris', 'postal_code' => '75001'],
        ]);

        $result = $this->processor->process(1, $payload);

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
    }

    public function testShippingOptionIdUsesDeliveryOptionFormat(): void
    {
        $carriers = [3 => ['name' => 'La Poste', 'price' => 4.99]];
        $cart = $this->makeCart($carriers, [1 => '3,']);

        $this->cartAdapter->method('getCart')->willReturn($cart);

        $payload = new ShippingCallbackPayload(['id' => 'ORDER-1']);

        $result = $this->processor->process(1, $payload);

        $option = $result['purchase_units'][0]['shipping_options'][0];
        $this->assertSame('delivery-option-3', $option['id']);
        $this->assertSame('La Poste', $option['label']);
        $this->assertSame('SHIPPING', $option['type']);
        $this->assertSame('4.99', $option['amount']['value']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param array<int, array{name: string, price: float}> $carriers
     * @param array<int, string> $selectedDeliveryOption [addressId => 'carrierId,']
     *
     * @return Cart|MockObject
     */
    private function makeCart(array $carriers, array $selectedDeliveryOption): Cart
    {
        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cart->id = 1;
        $cart->id_currency = 0;

        $deliveryOptionList = [];
        if (!empty($carriers)) {
            $carrierList = [];
            foreach ($carriers as $carrierId => $data) {
                $instance = new \stdClass();
                $instance->name = $data['name'];
                $carrierList[$carrierId] = [
                    'price_with_tax' => $data['price'],
                    'instance' => $instance,
                ];
            }
            $deliveryOptionList = [
                1 => [
                    'carrier_option' => ['carrier_list' => $carrierList],
                ],
            ];
        }

        $cart->method('getDeliveryOptionList')->willReturn($deliveryOptionList);
        $cart->method('getDeliveryOption')->willReturn($selectedDeliveryOption);
        $cart->method('getOrderTotal')->willReturn(100.00);

        return $cart;
    }

    /**
     * @param array<int, array<string, mixed>> $options
     *
     * @return array<string, mixed>|null
     */
    private function findOptionById(array $options, string $id): ?array
    {
        foreach ($options as $option) {
            if ($option['id'] === $id) {
                return $option;
            }
        }

        return null;
    }
}
