<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;

class OrderPayloadBuilderTest extends TestCase
{
    public function testOrderPayloadBuilderExceptions()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider());
    }

    public function cartProvider()
    {
        return ['key' => 'value'];
    }

    public function nodeProvider()
    {
        return [
            'intent' => 'CAPTURE', // capture or authorize
            'custom_id' => 'abcd', // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => 'Checking out with your cart abcd from  ShopName',
            'amount' => [
                'currency_code' => 'EUR',
                'value' => 123,
            ],
            'payee' => [
                'merchant_id' => 'ABCD',
            ],
        ];
    }
}
