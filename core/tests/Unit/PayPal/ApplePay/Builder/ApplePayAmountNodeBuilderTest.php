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
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayAmountNodeBuilder;
use PsCheckout\Presentation\TranslatorInterface;

class ApplePayAmountNodeBuilderTest extends TestCase
{
    private function makeTranslator(): TranslatorInterface
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturnArgument(0);

        return $translator;
    }

    private function makeContext(bool $isVirtual = false): CheckoutContext
    {
        return new CheckoutContext(
            ['cart' => ['id' => 1, 'is_virtual' => $isVirtual]],
            'applepay',
            false,
            null,
            null,
            false,
            false
        );
    }

    /**
     * @param array<string, mixed> $breakdown
     */
    private function makeBuilder(array $breakdown = [], string $total = '99.99', string $currency = 'EUR'): ApplePayAmountNodeBuilder
    {
        $payload = [
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $currency,
                    'value' => $total,
                    'breakdown' => $breakdown,
                ],
            ]],
        ];

        $orderPayloadBuilder = $this->createMock(OrderPayloadBuilderInterface::class);
        $orderPayloadBuilder->method('build')->willReturn($payload);

        return new ApplePayAmountNodeBuilder($orderPayloadBuilder, $this->makeTranslator());
    }

    public function testBuildReturnsCurrencyAndTotal(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertSame('EUR', $result['currency_code']);
        $this->assertSame([
            'type' => 'final',
            'label' => 'Total',
            'amount' => '99.99',
        ], $result['total']);
    }

    public function testNoLineItemsWhenBreakdownIsEmpty(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey('line_items', $result);
    }

    public function testLineItemsIncludeSubtotalAndTax(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '16.00'],
            'shipping' => ['value' => '0.00'],
            'handling' => ['value' => '0.00'],
            'discount' => ['value' => '0.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext());

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'];
        $this->assertContains(['type' => 'final', 'label' => 'Subtotal', 'amount' => '80.00'], $lineItems);
        $this->assertContains(['type' => 'final', 'label' => 'Tax', 'amount' => '16.00'], $lineItems);
    }

    public function testShippingLineItemIncludedForPhysicalCart(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '0.00'],
            'shipping' => ['value' => '5.99'],
            'handling' => ['value' => '0.00'],
            'discount' => ['value' => '0.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext(false));

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'];
        $this->assertContains(['type' => 'final', 'label' => 'Shipping', 'amount' => '5.99'], $lineItems);
    }

    public function testShippingLineItemOmittedForVirtualCart(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '0.00'],
            'shipping' => ['value' => '5.99'],
            'handling' => ['value' => '0.00'],
            'discount' => ['value' => '0.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext(true));

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'] ?? [];
        foreach ($lineItems as $item) {
            $this->assertNotSame('Shipping', $item['label']);
        }
    }

    public function testDiscountAmountIsNegated(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '0.00'],
            'shipping' => ['value' => '0.00'],
            'handling' => ['value' => '0.00'],
            'discount' => ['value' => '5.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext());

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'];
        $this->assertContains(['type' => 'final', 'label' => 'Discount', 'amount' => '-5.00'], $lineItems);
    }

    public function testHandlingLineItemIncludedWhenPositive(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '0.00'],
            'shipping' => ['value' => '0.00'],
            'handling' => ['value' => '3.00'],
            'discount' => ['value' => '0.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext());

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'];
        $this->assertContains(['type' => 'final', 'label' => 'Handling', 'amount' => '3.00'], $lineItems);
    }

    public function testZeroTaxOmittedFromLineItems(): void
    {
        $breakdown = [
            'item_total' => ['value' => '80.00'],
            'tax_total' => ['value' => '0.00'],
            'shipping' => ['value' => '0.00'],
            'handling' => ['value' => '0.00'],
            'discount' => ['value' => '0.00'],
        ];

        $result = $this->makeBuilder($breakdown)->build($this->makeContext());

        /** @var array<int, array<string, string>> $lineItems */
        $lineItems = $result['line_items'] ?? [];
        foreach ($lineItems as $item) {
            $this->assertNotSame('Tax', $item['label']);
        }
    }
}
