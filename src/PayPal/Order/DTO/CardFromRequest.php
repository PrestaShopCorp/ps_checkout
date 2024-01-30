<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;
class CardFromRequest
{
        /**
     * The year and month, in ISO-8601 &#x60;YYYY-MM&#x60; date format. See [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @var string|null
     */
    protected $expiry;

    /**
     * The last digits of the payment card.
     *
     * @var string|null
     */
    protected $last_digits;

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->expiry = isset($data['expiry']) ? $data['expiry'] : null;
        $this->last_digits = isset($data['last_digits']) ? $data['last_digits'] : null;
    }

    /**
     * Gets expiry.
     *
     * @return string|null
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Sets expiry.
     *
     * @param string|null $expiry  The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return $this
     */
    public function setExpiry($expiry = null)
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Gets last_digits.
     *
     * @return string|null
     */
    public function getLastDigits()
    {
        return $this->last_digits;
    }

    /**
     * Sets last_digits.
     *
     * @param string|null $last_digits  The last digits of the payment card.
     *
     * @return $this
     */
    public function setLastDigits($last_digits = null)
    {
        $this->last_digits = $last_digits;

        return $this;
    }
}


