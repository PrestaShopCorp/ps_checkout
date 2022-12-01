<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\CheckoutAddress;

class AddressBuilderTest extends TestCase
{
    public function testAddressBuilderGenerateChecksum()
    {
        $address = new CheckoutAddress($this->addresProvider());
    }
}
