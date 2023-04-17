<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\Query;

use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\ValueObject\PayPalCaptureId;

class GetOrderPaymentQuery
{
    /** @var PayPalCaptureId */
    private $transactionId;

    /**
     * @throws PayPalCaptureException
     */
    public function __construct($transactionId)
    {
        $this->transactionId = new PayPalCaptureId($transactionId);
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
