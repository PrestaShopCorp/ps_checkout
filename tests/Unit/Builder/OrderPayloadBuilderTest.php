<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;

class OrderPayloadBuilderTest extends TestCase
{
    public function testOrderPayloadBuilderExceptions()
    {
        $orderPayloadBuilder = new OrderPayloadBuilder($this->cartProvider());
        $orderPayloadBuilder->buildBaseNode();
    }

    public function cartProvider()
    {
        return ['key' => 'value'];
    }
}
