<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event;

class PaymentTokenUpdatedEvent extends PaymentTokenEvent
{
    private $merchantId;

    public function __construct($resource, $merchantId)
    {
        parent::__construct($resource);
        $this->merchantId = $merchantId;
    }
}
