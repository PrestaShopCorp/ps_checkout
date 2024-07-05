<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO;

class GooglePayDisplayItem
{
    const TYPE_LINE_ITEM = 'LINE_ITEM';
    const TYPE_SUBTOTAL = 'SUBTOTAL';
    const TYPE_TAX = 'TAX';
    const STATUS_FINAL = 'FINAL';
    const STATUS_PENDING = 'PENDING';

    /**
     * @var string
     */
    private $label;
    /**
     * @var 'LINE_ITEM'|'SUBTOTAL'
     */
    private $type;
    /**
     * @var string
     */
    private $price;
    /**
     * @var 'FINAL'|'PENDING'
     */
    private $status = self::STATUS_FINAL;

    /**
     * @param string $label
     * @return GooglePayDisplayItem
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $type
     * @return GooglePayDisplayItem
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $price
     * @return GooglePayDisplayItem
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string $status
     * @return GooglePayDisplayItem
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function toArray()
    {
        return array_filter([
            'label' => $this->label,
            'type' => $this->type,
            'price' => $this->price,
            'status' => $this->status,
        ]);
    }
}
