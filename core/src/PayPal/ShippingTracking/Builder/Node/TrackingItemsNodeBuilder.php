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

namespace PsCheckout\Core\PayPal\ShippingTracking\Builder\Node;

use Psr\Log\LoggerInterface;

class TrackingItemsNodeBuilder implements TrackingItemsNodeBuilderInterface
{
    /**
     * @var array
     */
    private $products = [];

    /**
     * @var int
     */
    private $languageId;

    /**
     * @var int
     */
    private $shopId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setProducts(array $products): TrackingItemsNodeBuilderInterface
    {
        $this->products = $products;

        return $this;
    }

    /**
     * Set order context for language and shop
     *
     * @param int $languageId
     * @param int $shopId
     *
     * @return TrackingItemsNodeBuilderInterface
     */
    public function setOrderContext(int $languageId, int $shopId): TrackingItemsNodeBuilderInterface
    {
        $this->languageId = $languageId;
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $items = [];

        foreach ($this->products as $product) {
            $productId = (int) $product['id_product'];
            $productAttributeId = (int) $product['id_product_attribute'];
            
            // Get product details
            $productData = $this->getProductData($productId, $productAttributeId);
            
            // Validate required fields
            $sku = $this->validateSku($product, $productData);
            $quantity = $this->validateQuantity($product);
            $name = $this->validateName($product, $productData);

            $item = [
                'sku' => $sku,
                'quantity' => $quantity,
                'name' => $name,
                'description' => $this->getProductDescription($productData),
                'url' => $this->getProductUrl($productData['product'], $productAttributeId),
                'image_url' => $this->getProductImageUrl($productData['product'], $productAttributeId),
            ];

            // Conditionally add UPC only if both parts are present
            if (!empty($productData['upc_type']) && !empty($productData['upc_code'])) {
                $item['upc'] = [
                    'type' => $productData['upc_type'],
                    'code' => $productData['upc_code'],
                ];
            }

            $items[] = $item;
        }

        return ['items' => $items];
    }

    /**
     * Validate and get SKU (required field)
     *
     * @param array $product
     * @param array $productData
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function validateSku(array $product, array $productData): string
    {
        $sku = $product['reference'] ?? '';
        
        // If no SKU from order data, try to get from product data
        if (empty($sku)) {
            $sku = $productData['reference'] ?? '';
        }
        
        // SKU is required and must not be empty
        if (empty($sku)) {
            throw new \InvalidArgumentException(
                'SKU is required for tracking items. Product ID: ' . ($product['id_product'] ?? 'unknown')
            );
        }
        
        return $sku;
    }

    /**
     * Validate and get quantity (required field)
     *
     * @param array $product
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function validateQuantity(array $product): string
    {
        $quantity = (int) $product['quantity'];
        
        // Quantity must be a positive whole number
        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                'Quantity must be a positive whole number. Got: ' . $quantity .
                ' for product ID: ' . ($product['id_product'] ?? 'unknown')
            );
        }
        
        return (string) $quantity;
    }

    /**
     * Validate and get name (required field)
     *
     * @param array $product
     * @param array $productData
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function validateName(array $product, array $productData): string
    {
        $name = $product['name'] ?? $productData['name'] ?? '';
        
        // Name is required and must not be empty
        if (empty($name)) {
            throw new \InvalidArgumentException(
                'Product name is required for tracking items. Product ID: ' . ($product['id_product'] ?? 'unknown')
            );
        }
        
        return $name;
    }

    /**
     * Get product data including UPC information
     *
     * @param int $productId
     * @param int $productAttributeId
     *
     * @return array
     */
    private function getProductData(int $productId, int $productAttributeId): array
    {
        if ($productId <= 0) {
            return [];
        }

        try {
            // Use order's language instead of global context
            $languageId = $this->languageId ?: \Context::getContext()->language->id;
            $product = new \Product($productId, false, $languageId);
            
            if (!\Validate::isLoadedObject($product)) {
                return [];
            }

            $productData = [
                'product' => $product, // Pass product object for URL generation
                'reference' => $product->reference,
                'name' => $product->name,
                'description' => $product->description_short,
            ];

            // Simple UPC type detection - just recognize basic types
            $upcType = $this->getSimpleUpcType($product);
            $upcCode = '';
            
            if (!empty($upcType)) {
                $upcCode = $this->getUpcCodeByType($product, $upcType);
            }
            
            $productData['upc_type'] = $upcType;
            $productData['upc_code'] = $upcCode;

            return $productData;
        } catch (\Exception $e) {
            // Log error but don't break the process
            $this->logger->error('Error getting product data for tracking: ' . $e->getMessage(), [
                'product_id' => $productId,
            ]);
            
            return [];
        }
    }

    /**
     * Simple UPC type detection based on length
     *
     * @param \Product $product
     *
     * @return string
     */
    private function getSimpleUpcType(\Product $product): string
    {
        // Check different product fields for barcodes
        $upc = trim($product->upc ?? '');
        $ean13 = trim($product->ean13 ?? '');
        $isbn = trim($product->isbn ?? '');
        $mpn = trim($product->mpn ?? '');

        // Priority order: UPC -> EAN13 -> ISBN -> MPN
        if (!empty($upc)) {
            return $this->detectUpcType($upc);
        }
        
        if (!empty($ean13)) {
            return $this->detectUpcType($ean13);
        }
        
        if (!empty($isbn)) {
            return 'ISBN';
        }
        
        if (!empty($mpn)) {
            return 'MPN';
        }
        
        return '';
    }

    /**
     * Detect UPC type based on barcode value
     *
     * @param string $barcode
     *
     * @return string
     */
    private function detectUpcType(string $barcode): string
    {
        $length = strlen($barcode);
        
        // Simple type detection based on length
        switch ($length) {
            case 8:
                return 'UPC-E';
            case 12:
                return 'UPC-A';
            case 13:
                return 'EAN-13';
            case 14:
                return 'GTIN-14';
            default:
                return '';
        }
    }

    /**
     * Get product URL using order's shop context
     *
     * @param \Product $product
     * @param int $productAttributeId
     *
     * @return string
     */
    private function getProductUrl(\Product $product, int $productAttributeId = 0): string
    {
        try {
            $context = \Context::getContext();
            $link = $context->link;
            
            // Use shop ID from order if available
            $shopId = $this->shopId ?: null;
            
            return $link->getProductLink(
                $product,
                null,
                null,
                null,
                $this->languageId ?: $context->language->id,
                $shopId
            );
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get product image URL using order's shop context
     *
     * @param \Product $product
     * @param int $productAttributeId
     *
     * @return string
     */
    private function getProductImageUrl(\Product $product, int $productAttributeId = 0): string
    {
        try {
            $context = \Context::getContext();
            $link = $context->link;
            
            // Get product images
            $images = $product->getImages($this->languageId ?: $context->language->id);
            
            if (!empty($images)) {
                $image = reset($images);

                return $link->getImageLink(
                    $product->link_rewrite,
                    $image['id_image'],
                    'home_default'
                );
            }
            
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Get product description with fallback
     *
     * @param array $productData
     *
     * @return string
     */
    private function getProductDescription(array $productData): string
    {
        $description = $productData['description'] ?? '';
        
        // Strip HTML tags and limit length
        $description = strip_tags($description);
        $description = trim($description);
        
        if (strlen($description) > 255) {
            $description = substr($description, 0, 252) . '...';
        }
        
        // Fallback to name if description is empty
        return $description ?: ($productData['name'] ?? '');
    }

    /**
     * Get UPC code based on detected type
     *
     * @param \Product $product
     * @param string $upcType
     *
     * @return string
     */
    private function getUpcCodeByType(\Product $product, string $upcType): string
    {
        switch ($upcType) {
            case 'UPC-A':
            case 'UPC-E':
            case 'OTHER':
                return trim($product->upc ?? '');
                
            case 'EAN-13':
                return trim($product->ean13 ?? '');
                
            case 'ISBN':
                return trim($product->isbn ?? '');
                
            case 'MPN':
                return trim($product->mpn ?? '');
                
            case 'Reference':
                return trim($product->reference ?? '');
                
            case 'GTIN-14':
                // GTIN-14 could be in UPC or EAN13 field
                $gtin = trim($product->upc ?? '');
                if (empty($gtin)) {
                    $gtin = trim($product->ean13 ?? '');
                }

                return $gtin;
                
            default:
                return '';
        }
    }
}
