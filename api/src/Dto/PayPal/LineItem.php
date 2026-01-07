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
 * The line items for this purchase. If your merchant account has been configured for Level 3
 * processing this field will be passed to the processor on your behalf.
 */
class LineItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $quantity;

    /**
     * @var string|null
     */
    private $description;

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
     * @var OrderBillingPlan|null
     */
    private $billingPlan;

    /**
     * @var Money|null
     */
    private $unitAmount;

    /**
     * @var Money|null
     */
    private $tax;

    /**
     * @var string|null
     */
    private $commodityCode;

    /**
     * @var Money|null
     */
    private $discountAmount;

    /**
     * @var Money|null
     */
    private $totalAmount;

    /**
     * @var string|null
     */
    private $unitOfMeasure;

    /**
     * @param string $name
     * @param string $quantity
     */
    public function __construct(string $name, string $quantity)
    {
        $this->name = $name;
        $this->quantity = $quantity;
    }

    /**
     * Returns Name.
     * The item name or title.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The item name or title.
     *
     * @required
     * @maps name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns Quantity.
     * The item quantity. Must be a whole number.
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * Sets Quantity.
     * The item quantity. Must be a whole number.
     *
     * @required
     * @maps quantity
     * @return self
     */
    public function setQuantity(string $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Returns Description.
     * The detailed item description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * The detailed item description.
     *
     * @maps description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns Sku.
     * The stock keeping unit (SKU) for the item.
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Sets Sku.
     * The stock keeping unit (SKU) for the item.
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

    /**
     * Returns Billing Plan.
     * Metadata for merchant-managed recurring billing plans. Valid only during the saved payment method
     * token or billing agreement creation.
     */
    public function getBillingPlan(): ?OrderBillingPlan
    {
        return $this->billingPlan;
    }

    /**
     * Sets Billing Plan.
     * Metadata for merchant-managed recurring billing plans. Valid only during the saved payment method
     * token or billing agreement creation.
     *
     * @maps billing_plan
     * @return self
     */
    public function setBillingPlan(?OrderBillingPlan $billingPlan): self
    {
        $this->billingPlan = $billingPlan;

        return $this;
    }

    /**
     * Returns Unit Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getUnitAmount(): ?Money
    {
        return $this->unitAmount;
    }

    /**
     * Sets Unit Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps unit_amount
     * @return self
     */
    public function setUnitAmount(?Money $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    /**
     * Returns Tax.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getTax(): ?Money
    {
        return $this->tax;
    }

    /**
     * Sets Tax.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps tax
     * @return self
     */
    public function setTax(?Money $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Returns Commodity Code.
     * Code used to classify items purchased and track the total amount spent across various categories of
     * products and services. Different corporate purchasing organizations may use different standards, but
     * the United Nations Standard Products and Services Code (UNSPSC) is frequently used.
     */
    public function getCommodityCode(): ?string
    {
        return $this->commodityCode;
    }

    /**
     * Sets Commodity Code.
     * Code used to classify items purchased and track the total amount spent across various categories of
     * products and services. Different corporate purchasing organizations may use different standards, but
     * the United Nations Standard Products and Services Code (UNSPSC) is frequently used.
     *
     * @maps commodity_code
     * @return self
     */
    public function setCommodityCode(?string $commodityCode): self
    {
        $this->commodityCode = $commodityCode;

        return $this;
    }

    /**
     * Returns Discount Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getDiscountAmount(): ?Money
    {
        return $this->discountAmount;
    }

    /**
     * Sets Discount Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps discount_amount
     * @return self
     */
    public function setDiscountAmount(?Money $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * Returns Total Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getTotalAmount(): ?Money
    {
        return $this->totalAmount;
    }

    /**
     * Sets Total Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps total_amount
     * @return self
     */
    public function setTotalAmount(?Money $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    /**
     * Returns Unit of Measure.
     * Unit of measure is a standard used to express the magnitude of a quantity in international trade.
     * Most commonly used (but not limited to) examples are: Acre (ACR), Ampere (AMP), Centigram (CGM),
     * Centimetre (CMT), Cubic inch (INQ), Cubic metre (MTQ), Fluid ounce (OZA), Foot (FOT), Hour (HUR),
     * Item (ITM), Kilogram (KGM), Kilometre (KMT), Kilowatt (KWT), Liquid gallon (GLL), Liter (LTR),
     * Pounds (LBS), Square foot (FTK).
     */
    public function getUnitOfMeasure(): ?string
    {
        return $this->unitOfMeasure;
    }

    /**
     * Sets Unit of Measure.
     * Unit of measure is a standard used to express the magnitude of a quantity in international trade.
     * Most commonly used (but not limited to) examples are: Acre (ACR), Ampere (AMP), Centigram (CGM),
     * Centimetre (CMT), Cubic inch (INQ), Cubic metre (MTQ), Fluid ounce (OZA), Foot (FOT), Hour (HUR),
     * Item (ITM), Kilogram (KGM), Kilometre (KMT), Kilowatt (KWT), Liquid gallon (GLL), Liter (LTR),
     * Pounds (LBS), Square foot (FTK).
     *
     * @maps unit_of_measure
     * @return self
     */
    public function setUnitOfMeasure(?string $unitOfMeasure): self
    {
        $this->unitOfMeasure = $unitOfMeasure;

        return $this;
    }
}
