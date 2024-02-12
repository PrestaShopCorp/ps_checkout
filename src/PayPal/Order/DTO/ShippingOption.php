<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class ShippingOption
{
    /**
     * A unique ID that identifies a payer-selected shipping option.
     *
     * @var string
     */
    protected $id;

    /**
     * A description that the payer sees, which helps them choose an appropriate shipping option. For example, &#x60;Free Shipping&#x60;, &#x60;USPS Priority Shipping&#x60;, &#x60;Expédition prioritaire USPS&#x60;, or &#x60;USPS yōuxiān fā huò&#x60;. Localize this description to the payer&#39;s locale.
     *
     * @var string
     */
    protected $label;

    /**
     * If the API request sets &#x60;selected &#x3D; true&#x60;, it represents the shipping option that the payee or merchant expects to be pre-selected for the payer when they first view the &#x60;shipping.options&#x60; in the PayPal Checkout experience. As part of the response if a &#x60;shipping.option&#x60; contains &#x60;selected&#x3D;true&#x60;, it represents the shipping option that the payer selected during the course of checkout with PayPal. Only one &#x60;shipping.option&#x60; can be set to &#x60;selected&#x3D;true&#x60;.
     *
     * @var bool
     */
    protected $selected;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var Amount|null
     */
    protected $amount;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->label = isset($data['label']) ? $data['label'] : null;
        $this->selected = isset($data['selected']) ? $data['selected'] : null;
        $this->type = isset($data['type']) ? $data['type'] : null;
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
    }

    /**
     * Gets id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param string $id a unique ID that identifies a payer-selected shipping option
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets label.
     *
     * @param string $label A description that the payer sees, which helps them choose an appropriate shipping option. For example, `Free Shipping`, `USPS Priority Shipping`, `Expédition prioritaire USPS`, or `USPS yōuxiān fā huò`. Localize this description to the payer's locale.
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Gets selected.
     *
     * @return bool
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * Sets selected.
     *
     * @param bool $selected If the API request sets `selected = true`, it represents the shipping option that the payee or merchant expects to be pre-selected for the payer when they first view the `shipping.options` in the PayPal Checkout experience. As part of the response if a `shipping.option` contains `selected=true`, it represents the shipping option that the payer selected during the course of checkout with PayPal. Only one `shipping.option` can be set to `selected=true`.
     *
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Gets type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type.
     *
     * @param string|null $type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets amount.
     *
     * @return Amount|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets amount.
     *
     * @param Amount|null $amount
     *
     * @return $this
     */
    public function setAmount(Amount $amount = null)
    {
        $this->amount = $amount;

        return $this;
    }
}
