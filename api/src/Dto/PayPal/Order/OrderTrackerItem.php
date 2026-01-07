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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\UniversalProductCode;

/**
 * The details of the items in the shipment.
 */
class OrderTrackerItem
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $quantity;

    /**
     * @var string|null
     */
    private $sku;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var string|null
     */
    private $imageUrl;

    /**
     * @var UniversalProductCode|null
     */
    private $upc;

    /**
     * Returns Name.
     * The item name or title.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The item name or title.
     *
     * @maps name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns Quantity.
     * The item quantity. Must be a whole number.
     */
    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    /**
     * Sets Quantity.
     * The item quantity. Must be a whole number.
     *
     * @maps quantity
     */
    public function setQuantity(?string $quantity): void
    {
        $this->quantity = $quantity;
    }

    /**
     * Returns Sku.
     * The stock keeping unit (SKU) for the item. This can contain unicode characters.
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Sets Sku.
     * The stock keeping unit (SKU) for the item. This can contain unicode characters.
     *
     * @maps sku
     */
    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    /**
     * Returns Url.
     * The URL to the item being purchased. Visible to buyer and used in buyer experiences.
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sets Url.
     * The URL to the item being purchased. Visible to buyer and used in buyer experiences.
     *
     * @maps url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    /**
     * Returns Image Url.
     * The URL of the item's image. File type and size restrictions apply. An image that violates these
     * restrictions will not be honored.
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * Sets Image Url.
     * The URL of the item's image. File type and size restrictions apply. An image that violates these
     * restrictions will not be honored.
     *
     * @maps image_url
     */
    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Returns Upc.
     * The Universal Product Code of the item.
     */
    public function getUpc(): ?UniversalProductCode
    {
        return $this->upc;
    }

    /**
     * Sets Upc.
     * The Universal Product Code of the item.
     *
     * @maps upc
     */
    public function setUpc(?UniversalProductCode $upc): void
    {
        $this->upc = $upc;
    }
}
