<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Reason
{
    /**
     * The reason why the captured payment status is &#x60;PENDING&#x60; or &#x60;DENIED&#x60;.
     *
     * @var string|null
     */
    protected $reason;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->reason = isset($data['reason']) ? $data['reason'] : null;
    }

    /**
     * Gets reason.
     *
     * @return string|null
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Sets reason.
     *
     * @param string|null $reason the reason why the captured payment status is `PENDING` or `DENIED`
     *
     * @return $this
     */
    public function setReason($reason = null)
    {
        $this->reason = $reason;

        return $this;
    }
}
