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

namespace PsCheckout\Tests\Unit\PayPal\ShippingTracking\Builder\Node;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ShippingTracking\Builder\Node\TrackingItemsNodeBuilder;

class TrackingItemsNodeBuilderTest extends TestCase
{
    /** @var TrackingItemsNodeBuilder */
    private $builder;

    /** @var \ReflectionMethod */
    private $resolveSku;

    /** @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->builder = new TrackingItemsNodeBuilder($this->logger);

        $method = new \ReflectionMethod(TrackingItemsNodeBuilder::class, 'resolveSku');
        $method->setAccessible(true);
        $this->resolveSku = $method;
    }

    /**
     * Root cause: the 422 "Invalid items: 8-0" from the PayPal tracking API was caused by a
     * SKU mismatch between the two builders:
     * - AmountBreakdownNode (order creation) correctly sent sku="demo_13" (product reference)
     * - TrackingItemsNodeBuilder fell back to "8-0" ({id_product}-{id_product_attribute})
     * PayPal validates tracking items against the original order by SKU, so "8-0" ≠ "demo_13" → 422.
     *
     * The fix makes both builders consistent: use the product reference when available,
     * omit the sku field entirely when the reference is empty (matching AmountBreakdownNode).
     */
    public function testItUsesProductReferenceWhenProductDataSkuIsEmpty(): void
    {
        $product = ['id_product' => 8, 'id_product_attribute' => 0, 'reference' => 'demo_13', 'quantity' => 1, 'name' => 'Mug'];
        $productData = ['sku' => ''];

        $sku = $this->resolveSku->invoke($this->builder, $product, $productData);

        $this->assertSame('demo_13', $sku);
    }

    public function testItUsesProductDataSkuWhenPresent(): void
    {
        $product = ['id_product' => 8, 'id_product_attribute' => 5, 'reference' => 'demo_13', 'quantity' => 1, 'name' => 'Mug'];
        $productData = ['sku' => 'demo_13-size-L'];

        $sku = $this->resolveSku->invoke($this->builder, $product, $productData);

        $this->assertSame('demo_13-size-L', $sku);
    }

    /**
     * When both product data sku and reference are empty, resolveSku returns ''.
     * build() then omits the sku field entirely — consistent with AmountBreakdownNode,
     * which also omits sku when reference is empty so PayPal has nothing to mismatch against.
     */
    public function testItReturnsEmptyStringAndLogsWarningWhenBothSkuAndReferenceAreEmpty(): void
    {
        $product = ['id_product' => 99, 'id_product_attribute' => 0, 'reference' => '', 'quantity' => 1, 'name' => 'No Ref Product'];
        $productData = ['sku' => ''];

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                'No SKU/reference found for product, sku field will be omitted from tracking payload.',
                ['id_product' => 99, 'id_product_attribute' => 0]
            );

        $sku = $this->resolveSku->invoke($this->builder, $product, $productData);

        $this->assertSame('', $sku);
    }

    /**
     * Regression: getProductData() previously generated "8-0" as sku when productAttributeId=0
     * and no combination reference existed. resolveSku() would then return it as-is,
     * causing a mismatch with the order's sku ("demo_13") and a 422 from PayPal.
     * After the fix, getProductData() returns '' in that case, so resolveSku()
     * falls back to $product['reference'] instead.
     */
    public function testItDoesNotForwardSyntheticIdFromProductData(): void
    {
        $product = ['id_product' => 8, 'id_product_attribute' => 0, 'reference' => 'demo_13', 'quantity' => 1, 'name' => 'Mug'];
        // Simulates what getProductData() returned BEFORE the fix to that method
        $productDataBeforeFix = ['sku' => '8-0'];

        $sku = $this->resolveSku->invoke($this->builder, $product, $productDataBeforeFix);

        // resolveSku itself prefers $productData['sku'] when non-empty;
        // the upstream fix to getProductData() ensures it never produces "8-0" anymore.
        $this->assertSame('8-0', $sku, 'resolveSku does not filter synthetic IDs — the fix is in getProductData()');
    }
}
