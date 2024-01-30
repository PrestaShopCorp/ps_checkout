<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;
class LineItem
{
    /**
     * The item name or title.
     *
     * @var string
     */
    protected $name;
    /**
     * @var Amount
     */
    protected $unit_amount;
    /**
     * The item quantity. Must be a whole number.
     *
     * @var string
     */
    protected $quantity;
    /**
     * @var Amount|null
     */
    protected $tax;
    /**
     * The detailed item description.
     *
     * @var string|null
     */
    protected $description;
    /**
     * The stock keeping unit (SKU) for the item.
     *
     * @var string|null
     */
    protected $sku;
    /**
     * The item category type.
     *
     * @var string|null
     */
    protected $category;
    /**
     * Code used to classify items purchased and track the total amount spent across various categories of products and services. Different corporate purchasing organizations may use different standards, but the United Nations Standard Products and Services Code (UNSPSC) is frequently used.
     *
     * @var string|null
     */
    protected $commodity_code;
    /**
     * @var Amount|null
     */
    protected $discount_amount;
    /**
     * @var Amount|null
     */
    protected $total_amount;
    /**
     * Unit of measure is a standard used to express the magnitude of a quantity in international trade. Most commonly used (but not limited to) examples are: Acre (ACR), Ampere (AMP), Centigram (CGM), Centimetre (CMT), Cubic inch (INQ), Cubic metre (MTQ), Fluid ounce (OZA), Foot (FOT), Hour (HUR), Item (ITM), Kilogram (KGM), Kilometre (KMT), Kilowatt (KWT), Liquid gallon (GLL), Liter (LTR), Pounds (LBS), Square foot (FTK).
     *
     * @var string|null
     */
    protected $unit_of_measure;
    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->unit_amount = isset($data['unit_amount']) ? $data['unit_amount'] : null;
        $this->quantity = isset($data['quantity']) ? $data['quantity'] : null;
        $this->tax = isset($data['tax']) ? $data['tax'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->sku = isset($data['sku']) ? $data['sku'] : null;
        $this->category = isset($data['category']) ? $data['category'] : null;
        $this->commodity_code = isset($data['commodity_code']) ? $data['commodity_code'] : null;
        $this->discount_amount = isset($data['discount_amount']) ? $data['discount_amount'] : null;
        $this->total_amount = isset($data['total_amount']) ? $data['total_amount'] : null;
        $this->unit_of_measure = isset($data['unit_of_measure']) ? $data['unit_of_measure'] : null;
    }
    /**
     * Gets name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Sets name.
     *
     * @param string $name  The item name or title.
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Gets unit_amount.
     *
     * @return Amount
     */
    public function getUnitAmount()
    {
        return $this->unit_amount;
    }
    /**
     * Sets unit_amount.
     *
     * @param Amount $unit_amount
     *
     * @return $this
     */
    public function setUnitAmount(Amount $unit_amount)
    {
        $this->unit_amount = $unit_amount;
        return $this;
    }
    /**
     * Gets quantity.
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    /**
     * Sets quantity.
     *
     * @param string $quantity  The item quantity. Must be a whole number.
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        return $this;
    }
    /**
     * Gets tax.
     *
     * @return Amount|null
     */
    public function getTax()
    {
        return $this->tax;
    }
    /**
     * Sets tax.
     *
     * @param Amount|null $tax
     *
     * @return $this
     */
    public function setTax(Amount $tax = null)
    {
        $this->tax = $tax;
        return $this;
    }
    /**
     * Gets description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * Sets description.
     *
     * @param string|null $description  The detailed item description.
     *
     * @return $this
     */
    public function setDescription($description = null)
    {
        $this->description = $description;
        return $this;
    }
    /**
     * Gets sku.
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->sku;
    }
    /**
     * Sets sku.
     *
     * @param string|null $sku  The stock keeping unit (SKU) for the item.
     *
     * @return $this
     */
    public function setSku($sku = null)
    {
        $this->sku = $sku;
        return $this;
    }
    /**
     * Gets category.
     *
     * @return string|null
     */
    public function getCategory()
    {
        return $this->category;
    }
    /**
     * Sets category.
     *
     * @param string|null $category  The item category type.
     *
     * @return $this
     */
    public function setCategory($category = null)
    {
        $this->category = $category;
        return $this;
    }
    /**
     * Gets commodity_code.
     *
     * @return string|null
     */
    public function getCommodityCode()
    {
        return $this->commodity_code;
    }
    /**
     * Sets commodity_code.
     *
     * @param string|null $commodity_code  Code used to classify items purchased and track the total amount spent across various categories of products and services. Different corporate purchasing organizations may use different standards, but the United Nations Standard Products and Services Code (UNSPSC) is frequently used.
     *
     * @return $this
     */
    public function setCommodityCode($commodity_code = null)
    {
        $this->commodity_code = $commodity_code;
        return $this;
    }
    /**
     * Gets discount_amount.
     *
     * @return Amount|null
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }
    /**
     * Sets discount_amount.
     *
     * @param Amount|null $discount_amount
     *
     * @return $this
     */
    public function setDiscountAmount(Amount $discount_amount = null)
    {
        $this->discount_amount = $discount_amount;
        return $this;
    }
    /**
     * Gets total_amount.
     *
     * @return Amount|null
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }
    /**
     * Sets total_amount.
     *
     * @param Amount|null $total_amount
     *
     * @return $this
     */
    public function setTotalAmount(Amount $total_amount = null)
    {
        $this->total_amount = $total_amount;
        return $this;
    }
    /**
     * Gets unit_of_measure.
     *
     * @return string|null
     */
    public function getUnitOfMeasure()
    {
        return $this->unit_of_measure;
    }
    /**
     * Sets unit_of_measure.
     *
     * @param string|null $unit_of_measure  Unit of measure is a standard used to express the magnitude of a quantity in international trade. Most commonly used (but not limited to) examples are: Acre (ACR), Ampere (AMP), Centigram (CGM), Centimetre (CMT), Cubic inch (INQ), Cubic metre (MTQ), Fluid ounce (OZA), Foot (FOT), Hour (HUR), Item (ITM), Kilogram (KGM), Kilometre (KMT), Kilowatt (KWT), Liquid gallon (GLL), Liter (LTR), Pounds (LBS), Square foot (FTK).
     *
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure = null)
    {
        $this->unit_of_measure = $unit_of_measure;
        return $this;
    }
}
