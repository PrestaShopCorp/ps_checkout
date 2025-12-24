<?php

namespace PsCheckout\Core\PayPal\Payment\Authorization\Action;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;

interface ReauthorizeAuthorizationActionInterface
{
    /**
     * @param PayPalOrderResponse $payPalOrder
     *
     * @return PayPalOrderAuthorization
     */
    public function execute(PayPalOrderResponse $payPalOrder): PayPalOrderAuthorization;
}
