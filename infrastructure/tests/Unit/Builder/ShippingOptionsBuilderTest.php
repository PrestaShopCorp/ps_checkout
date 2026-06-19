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

namespace Tests\Unit\PsCheckout\Infrastructure\Builder;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\CartDataInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\HookInterface;
use PsCheckout\Infrastructure\Builder\ShippingOptionsBuilder;
use PsCheckout\Infrastructure\Repository\PsCheckoutCarrierRepository;

class ShippingOptionsBuilderTest extends TestCase
{
    /** @var CartInterface|MockObject */
    private $cartAdapter;

    /** @var PsCheckoutCarrierRepository|MockObject */
    private $carrierRepository;

    /** @var HookInterface|MockObject */
    private $hook;

    /** @var ShippingOptionsBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->cartAdapter = $this->createMock(CartInterface::class);
        $this->carrierRepository = $this->getMockBuilder(PsCheckoutCarrierRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->hook = $this->createMock(HookInterface::class);
        $this->builder = new ShippingOptionsBuilder($this->cartAdapter, $this->carrierRepository, $this->hook);
    }

    public function testBuildReturnsEmptyArrayWhenCartNotFound(): void
    {
        $this->cartAdapter->method('getCart')->willReturn(null);

        $this->assertSame([], $this->builder->build(99, null));
    }

    public function testBuildReturnsEmptyArrayWhenDeliveryOptionsEmpty(): void
    {
        $cart = $this->makeCart([], 0);
        $this->cartAdapter->method('getCart')->willReturn($cart);

        $this->assertSame([], $this->builder->build(1, null));
    }

    public function testBuildReturnsEmptyArrayWhenAllCarriersDisabled(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => true]
        );

        $this->assertSame([], $this->builder->build(1, null));
    }

    public function testBuildUsesPriceWithTax(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99, 'price' => 3.00]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertSame('4.99', $options[0]['amount']['value']);
    }

    public function testBuildFallsBackToNetPriceWhenPriceWithTaxAbsent(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price' => 3.50]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertSame('3.50', $options[0]['amount']['value']);
    }

    public function testBuildUsesCarrierInstanceName(): void
    {
        $instance = new \stdClass();
        $instance->name = 'La Poste';

        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99, 'instance' => $instance]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertSame('La Poste', $options[0]['label']);
    }

    public function testBuildFallsBackToCarrierIdAsLabelWhenNoInstance(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertSame('3', $options[0]['label']);
    }

    public function testBuildSkipsDisabledCarrierAndIncludesEnabled(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([
            3 => ['price_with_tax' => 4.99],
            5 => ['price_with_tax' => 7.00],
        ]), 5);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturnMap([
            [3, ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => true]],
            [5, ['id_reference' => 5, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]],
        ]);

        $options = $this->builder->build(1, null);

        $this->assertCount(1, $options);
        $this->assertSame('delivery-option-5', $options[0]['id']);
    }

    public function testBuildSelectsOptionBySelectedOptionId(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([
            3 => ['price_with_tax' => 4.99],
            5 => ['price_with_tax' => 7.00],
        ]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, 'delivery-option-5');

        $selected = array_filter($options, function ($o) { return $o['selected']; });
        $this->assertCount(1, $selected);
        $this->assertSame('delivery-option-5', array_values($selected)[0]['id']);
    }

    public function testBuildSelectsCurrentCartCarrierWhenNoSelectedOptionId(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([
            3 => ['price_with_tax' => 4.99],
            5 => ['price_with_tax' => 7.00],
        ]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $selected = array_filter($options, function ($o) { return $o['selected']; });
        $this->assertCount(1, $selected);
        $this->assertSame('delivery-option-3', array_values($selected)[0]['id']);
    }

    public function testBuildSelectsFirstOptionWhenNoMatchFound(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([
            3 => ['price_with_tax' => 4.99],
            5 => ['price_with_tax' => 7.00],
        ]), 0);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertTrue($options[0]['selected']);
        $this->assertFalse($options[1]['selected']);
    }

    public function testBuildOnlyProcessesFirstAddress(): void
    {
        $deliveryOptions = [
            1 => ['opt' => ['carrier_list' => [3 => ['price_with_tax' => 4.99]]]],
            2 => ['opt' => ['carrier_list' => [7 => ['price_with_tax' => 9.99]]]],
        ];

        $cart = $this->makeCart($deliveryOptions, 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_SHIPPING, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertCount(1, $options);
        $this->assertSame('delivery-option-3', $options[0]['id']);
    }

    public function testFiresHookForCarrierWithNoRepositoryData(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(null);

        $this->hook
            ->expects($this->once())
            ->method('exec')
            ->with(
                'actionGetPsCheckoutCarrierType',
                $this->callback(function ($params) {
                    return $params['id_carrier'] === 3 && $params['id_reference'] === 0;
                })
            );

        $this->builder->build(1, null);
    }

    public function testBuildSetsCarrierTypeFromRepository(): void
    {
        $cart = $this->makeCart($this->deliveryOptions([3 => ['price_with_tax' => 4.99]]), 3);
        $this->cartAdapter->method('getCart')->willReturn($cart);
        $this->carrierRepository->method('getCarrierData')->willReturn(
            ['id_reference' => 3, 'type' => PsCheckoutCarrierRepository::TYPE_PICKUP, 'disabled' => false]
        );

        $options = $this->builder->build(1, null);

        $this->assertSame(PsCheckoutCarrierRepository::TYPE_PICKUP, $options[0]['type']);
    }

    public function testGetSelectedShippingPriceReturnsSelectedOptionPrice(): void
    {
        $options = [
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => false],
            ['id' => 'delivery-option-5', 'amount' => ['value' => '7.50'], 'selected' => true],
        ];

        $this->assertSame(7.50, $this->builder->getSelectedShippingPrice($options));
    }

    public function testGetSelectedShippingPriceReturnsZeroWhenNoneSelected(): void
    {
        $options = [
            ['id' => 'delivery-option-3', 'amount' => ['value' => '4.99'], 'selected' => false],
        ];

        $this->assertSame(0.0, $this->builder->getSelectedShippingPrice($options));
    }

    public function testGetSelectedShippingPriceReturnsZeroForEmptyOptions(): void
    {
        $this->assertSame(0.0, $this->builder->getSelectedShippingPrice([]));
    }

    /**
     * @param array<string, mixed> $deliveryOptions
     *
     * @return CartDataInterface|MockObject
     */
    private function makeCart(array $deliveryOptions, int $selectedCarrierId): CartDataInterface
    {
        $cart = $this->createMock(CartDataInterface::class);

        $cart->method('getDeliveryOptionList')->willReturn($deliveryOptions);
        $cart->method('getDeliveryOption')->willReturn(
            $selectedCarrierId ? [1 => $selectedCarrierId . ','] : []
        );
        $cart->method('getCurrencyIsoCode')->willReturn('EUR');

        return $cart;
    }

    /**
     * Builds a minimal single-address delivery options array.
     *
     * @param array<int, array<string, mixed>> $carriers  carrierId => carrierData
     *
     * @return array<int, array<string, array<string, mixed>>>
     */
    private function deliveryOptions(array $carriers): array
    {
        return [1 => ['opt' => ['carrier_list' => $carriers]]];
    }
}
