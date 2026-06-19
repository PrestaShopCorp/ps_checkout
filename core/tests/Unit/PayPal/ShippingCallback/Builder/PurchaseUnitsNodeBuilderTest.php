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

namespace Tests\Unit\PsCheckout\Core\PayPal\ShippingCallback\Builder;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\PurchaseUnitsNodeBuilder;

class PurchaseUnitsNodeBuilderTest extends TestCase
{
    /** @var PurchaseUnitsNodeBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new PurchaseUnitsNodeBuilder();
    }

    public function testBuildReturnsExpectedStructure(): void
    {
        $shippingOptions = [
            ['id' => 'delivery-option-1', 'label' => 'Standard', 'type' => 'SHIPPING', 'amount' => ['currency_code' => 'EUR', 'value' => '5.00'], 'selected' => true],
        ];

        $result = $this->builder->build('d9f80740-38f0-11e8-b467-0ed5f89f718b', 'EUR', 80.00, 10.00, 5.00, $shippingOptions);

        $this->assertArrayHasKey('purchase_units', $result);
        $unit = $result['purchase_units'][0];

        $this->assertSame('d9f80740-38f0-11e8-b467-0ed5f89f718b', $unit['reference_id']);
        $this->assertSame('EUR', $unit['amount']['currency_code']);
        $this->assertSame('95.00', $unit['amount']['value']);
        $this->assertSame('EUR', $unit['amount']['breakdown']['item_total']['currency_code']);
        $this->assertSame('80.00', $unit['amount']['breakdown']['item_total']['value']);
        $this->assertSame('EUR', $unit['amount']['breakdown']['tax_total']['currency_code']);
        $this->assertSame('10.00', $unit['amount']['breakdown']['tax_total']['value']);
        $this->assertSame('EUR', $unit['amount']['breakdown']['shipping']['currency_code']);
        $this->assertSame('5.00', $unit['amount']['breakdown']['shipping']['value']);
        $this->assertSame($shippingOptions, $unit['shipping_options']);
    }

    public function testDefaultReferenceIdFallback(): void
    {
        $result = $this->builder->build('default', 'EUR', 10.00, 0.00, 0.00, []);
        $this->assertSame('default', $result['purchase_units'][0]['reference_id']);
    }

    public function testTotalIsSumOfComponents(): void
    {
        $result = $this->builder->build('default', 'GBP', 29.00, 5.80, 0.00, []);

        $unit = $result['purchase_units'][0];
        $this->assertSame('34.80', $unit['amount']['value']);
        $this->assertSame('29.00', $unit['amount']['breakdown']['item_total']['value']);
        $this->assertSame('5.80', $unit['amount']['breakdown']['tax_total']['value']);
        $this->assertSame('0.00', $unit['amount']['breakdown']['shipping']['value']);
        $this->assertSame([], $unit['shipping_options']);
    }

    public function testTotalIsRoundedToTwoDecimalPlaces(): void
    {
        // 10.005 + 0.003 + 0.001 = 10.009 → rounds to 10.01
        $result = $this->builder->build('default', 'USD', 10.005, 0.003, 0.001, []);
        $this->assertSame('10.01', $result['purchase_units'][0]['amount']['value']);
    }

    /**
     * @return array<string, array{float, float, float, string}>
     */
    public function amountProvider(): array
    {
        return [
            'zero shipping' => [100.00, 0.00, 0.00, '100.00'],
            'with tax and shipping' => [50.00, 10.00, 8.40, '68.40'],
            'large amounts' => [999.99, 200.00, 25.50, '1225.49'],
        ];
    }

    /**
     * @dataProvider amountProvider
     */
    public function testTotalAmountValue(float $item, float $tax, float $shipping, string $expected): void
    {
        $result = $this->builder->build('default', 'EUR', $item, $tax, $shipping, []);
        $this->assertSame($expected, $result['purchase_units'][0]['amount']['value']);
    }
}
