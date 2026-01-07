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

namespace PsCheckout\Api\Dto\PayPal;

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
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
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
     * @return self
     */
    public function setQuantity(?string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
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
     * @return self
     */
    public function setSku(?string $sku): self
    {
        $this->sku = $sku;

        return $this;
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
     * @return self
     */
    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
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
     * @return self
     */
    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
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
     * @return self
     */
    public function setUpc(?UniversalProductCode $upc): self
    {
        $this->upc = $upc;

        return $this;
    }
}
