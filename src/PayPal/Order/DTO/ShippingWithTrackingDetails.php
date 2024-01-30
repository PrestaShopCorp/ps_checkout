<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class ShippingWithTrackingDetails
{
    /**
     * @var Name|null
     */
    protected $name;

    /**
     * The method by which the payer wants to get their items from the payee e.g shipping, in-person pickup. Either type or options but not both may be present.
     *
     * @var string|null
     */
    protected $type;

    /**
     * An array of shipping options that the payee or merchant offers to the payer to ship or pick up their items.
     *
     * @var ShippingOption[]|null
     */
    protected $options;

    /**
     * @var AddressRequest|null
     */
    protected $address;

    /**
     * An array of trackers for a transaction.
     *
     * @var Tracker[]|null
     */
    protected $trackers;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->type = isset($data['type']) ? $data['type'] : null;
        $this->options = isset($data['options']) ? $data['options'] : null;
        $this->address = isset($data['address']) ? $data['address'] : null;
        $this->trackers = isset($data['trackers']) ? $data['trackers'] : null;
    }

    /**
     * Gets name.
     *
     * @return Name|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param Name|null $name
     *
     * @return $this
     */
    public function setName(Name $name = null)
    {
        $this->name = $name;

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
     * @param string|null $type  The method by which the payer wants to get their items from the payee e.g shipping, in-person pickup. Either type or options but not both may be present.
     *
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets options.
     *
     * @return ShippingOption[]|null
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets options.
     *
     * @param ShippingOption[]|null $options  An array of shipping options that the payee or merchant offers to the payer to ship or pick up their items.
     *
     * @return $this
     */
    public function setOptions(array $options = null)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets address.
     *
     * @return AddressRequest|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets address.
     *
     * @param AddressRequest|null $address
     *
     * @return $this
     */
    public function setAddress(AddressRequest $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets trackers.
     *
     * @return Tracker[]|null
     */
    public function getTrackers()
    {
        return $this->trackers;
    }

    /**
     * Sets trackers.
     *
     * @param Tracker[]|null $trackers  An array of trackers for a transaction.
     *
     * @return $this
     */
    public function setTrackers(array $trackers = null)
    {
        $this->trackers = $trackers;

        return $this;
    }
}


