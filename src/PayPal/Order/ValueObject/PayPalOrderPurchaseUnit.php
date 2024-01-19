<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject;

class PayPalOrderPurchaseUnit
{
    /**
     * @var string
     */
    private $referenceId;
    /**
     * @var string
     */
    private $currency;
    /**
     * @var string
     */
    private $value;
    /**
     * @var string
     */
    private $customId;

    public function __construct($referenceId, $customId, $currency, $value)
    {
        $this->referenceId = $referenceId;
        $this->currency = $currency;
        $this->value = $value;
        $this->customId = $customId;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return string
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string $customId
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    public function toArray()
    {
        return [
            'reference_id' => $this->referenceId,
            'custom_id' => $this->customId,
            'amount' => [
                'currency' => $this->currency,
                'value' => $this->value
            ]
        ];
    }


}
