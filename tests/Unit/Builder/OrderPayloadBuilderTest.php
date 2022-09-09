<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class OrderPayloadBuilderTest extends TestCase
{
    public function testOrderPayloadBuilderIntentException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed intent %s is unsupported', 'FAILURE'));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('FAILURE'));
    }

    public function testOrderPayloadBuilderCurrencyCodeException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed currency %s is invalid', 'XXX'));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('XXX'));
    }

    public function testOrderPayloadBuilderAmountException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed amount %s is less or equal to zero', -1));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider('-1'));
    }

    public function testOrderPayloadBuilderMerchantIdException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage(sprintf('Passed merchant id %s is invalid', ''));
        $orderPayloadBuilder->checkBaseNode($this->nodeProvider(''));
    }

    public function testOrderPayloadBuilderShippingNameException()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('shiping name is empty');
        $orderPayloadBuilder->checkShippingNode($this->shippingNodeProvider(''));
    }

    public function cartProvider()
    {
        return ['key' => 'value'];
    }

    public function nodeProvider($value)
    {
        return [
            'intent' => $value == 'FAILURE' ? $value : 'CAPTURE', // capture or authorize
            'custom_id' => $value == '123' ? $value : 'abcd', // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => $value == 2 ? $value : '',
            'description' => $value == 3 ? $value : 'Checking out with your cart abcd from  ShopName',
            'amount' => [
                'currency_code' => $value == 'XXX' ? $value : 'EUR',
                'value' => $value == -1 ? $value : 123,
            ],
            'payee' => [
                'merchant_id' => $value == '' ? $value : 'ABCD',
            ],
        ];
    }

    public function shippingNodeProvider($value)
    {
        return [
            'name' => [
                'full_name' => $value == '' ? $value : 'John Lennon',
            ],
            'address' => [
                'address_line_1' => '',
                'address_line_2' => 'Taraku 3',
                'admin_area_1' => 'Lithuania',
                'admin_area_2' => 'Kaunas',
                'country_code' => 'LT',
                'postal_code' => '50285',
            ],
        ];
    }
}
