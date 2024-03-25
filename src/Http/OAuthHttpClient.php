<?php

namespace PrestaShop\Module\PrestashopCheckout\Http;

use GuzzleHttp\Psr7\Request;
use PrestaShop\Module\PrestashopCheckout\Builder\Configuration\HttpClientConfigurationBuilderInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;

class OAuthHttpClient extends PsrHttpClientAdapter
{
    public function __construct(HttpClientConfigurationBuilderInterface $configurationBuilder)
    {
        parent::__construct($configurationBuilder->build());
    }

    public function getUserIdToken($merchantId, PayPalCustomerId $payPalCustomerId = null, $options = [])
    {
        $payload = [
            'payer_id' => $merchantId,
            'customer_id' => $payPalCustomerId ? $payPalCustomerId->getValue() : null,
        ];

        return $this->sendRequest(
            new Request(
                'POST',
                '/oauth2/token',
                $options,
                json_encode($payload)
            )
        );
    }
}
