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

namespace Tests\Unit\PsCheckout\Core\PayPal\ApplePay\Builder;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayShippingNodeBuilder;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayShippingTypeResolver;

class ApplePayShippingNodeBuilderTest extends TestCase
{
    /**
     * @param array<int, array<string, mixed>> $shippingOptions
     */
    private function makeContext(bool $isVirtual, array $shippingOptions = []): CheckoutContext
    {
        return new CheckoutContext(
            ['cart' => ['id' => 1, 'is_virtual' => $isVirtual]],
            'applepay',
            false,
            null,
            null,
            false,
            false,
            null,
            null,
            false,
            false,
            null,
            $shippingOptions
        );
    }

    private function makeBuilder(): ApplePayShippingNodeBuilder
    {
        return new ApplePayShippingNodeBuilder(new ApplePayShippingTypeResolver());
    }

    public function testVirtualCartReturnsEmptyArray(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext(true));

        $this->assertSame([], $result);
    }

    public function testPhysicalCartWithoutOptionsReturnsEmptyArray(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext(false, []));

        $this->assertSame([], $result);
    }

    public function testShippingTypeIsShippingWhenSelectedOptionTypeIsShipping(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Colissimo', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.90'], 'selected' => true],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertSame('shipping', $result['shipping_type']);
    }

    public function testShippingTypeIsStorePickupWhenSelectedOptionTypeIsPickup(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Pickup Point', 'type' => 'PICKUP', 'amount' => ['currency_code' => 'EUR', 'value' => '0.00'], 'selected' => true],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertSame('storePickup', $result['shipping_type']);
    }

    public function testShippingTypeIsOmittedWhenSelectedOptionTypeIsUnknown(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Custom', 'type' => 'DRONE', 'amount' => ['currency_code' => 'EUR', 'value' => '3.00'], 'selected' => true],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertArrayNotHasKey('shipping_type', $result);
    }

    public function testShippingTypeIsOmittedWhenNoOptionIsSelected(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Colissimo', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.90'], 'selected' => false],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertArrayNotHasKey('shipping_type', $result);
    }

    public function testPhysicalCartMapsShippingOptions(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Colissimo', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.90'], 'selected' => true],
            ['id' => 'carrier_2', 'label' => 'DHL Express', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '12.00'], 'selected' => false],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertSame('shipping', $result['shipping_type']);
        /** @var array<int, array<string, string>> $shippingMethods */
        $shippingMethods = $result['shipping_methods'];
        $this->assertCount(2, $shippingMethods);
        $this->assertSame([
            'identifier' => 'carrier_1',
            'label' => 'Colissimo',
            'detail' => '',
            'amount' => '5.90',
        ], $shippingMethods[0]);
    }

    public function testSelectedMethodComesFirst(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Colissimo', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.90'], 'selected' => false],
            ['id' => 'carrier_2', 'label' => 'DHL Express', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '12.00'], 'selected' => true],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        /** @var array<int, array<string, string>> $shippingMethods */
        $shippingMethods = $result['shipping_methods'];
        $this->assertSame('carrier_2', $shippingMethods[0]['identifier']);
        $this->assertSame('carrier_1', $shippingMethods[1]['identifier']);
    }

    public function testShippingTypeDerivesFromSelectedOptionNotFirstOption(): void
    {
        $options = [
            ['id' => 'carrier_1', 'label' => 'Pickup Point', 'type' => 'PICKUP', 'amount' => ['currency_code' => 'EUR', 'value' => '0.00'], 'selected' => false],
            ['id' => 'carrier_2', 'label' => 'Colissimo', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.90'], 'selected' => true],
        ];

        $result = $this->makeBuilder()->build($this->makeContext(false, $options));

        $this->assertSame('shipping', $result['shipping_type']);
    }
}
