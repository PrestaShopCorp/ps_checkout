<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class BlikOneClickResponse
{
    /**
     * The merchant generated, unique reference serving as a primary identifier for accounts connected between Blik and a merchant.
     *
     * @var string|null
     */
    protected $consumer_reference;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->consumer_reference = isset($data['consumer_reference']) ? $data['consumer_reference'] : null;
    }

    /**
     * Gets consumer_reference.
     *
     * @return string|null
     */
    public function getConsumerReference()
    {
        return $this->consumer_reference;
    }

    /**
     * Sets consumer_reference.
     *
     * @param string|null $consumer_reference the merchant generated, unique reference serving as a primary identifier for accounts connected between Blik and a merchant
     *
     * @return $this
     */
    public function setConsumerReference($consumer_reference = null)
    {
        $this->consumer_reference = $consumer_reference;

        return $this;
    }
}
