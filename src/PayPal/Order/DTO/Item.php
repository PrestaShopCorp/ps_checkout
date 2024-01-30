<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Item
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
}


