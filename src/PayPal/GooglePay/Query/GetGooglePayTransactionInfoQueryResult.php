<?php
namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Query;

use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO\GooglePayTransactionInfo;

class GetGooglePayTransactionInfoQueryResult
{
    /**
     * @var GooglePayTransactionInfo
     */
    private $payload;

    public function __construct(GooglePayTransactionInfo $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return GooglePayTransactionInfo
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
