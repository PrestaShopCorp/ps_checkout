<?php

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Adapter\CountryAdapter;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\CheckoutAddress;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\PaypalAddressBuilder;
use PrestaShop\Module\PrestashopCheckout\Repository\AddressRepository;
use PrestaShop\Module\PrestashopCheckout\Adapter\AddressAdapter;

class AddressBuilderTest extends TestCase
{
    public function testAddressBuilderGenerateChecksum()
    {
        $this->mockAddress = $this->createMock(CheckoutAddress::class);
        $this->mockAddressRepository = $this->createMock(AddressRepository::class);
        $this->mockCountry = $this->createMock(CountryAdapter::class);
        $this->mockAddressAdapyer = $this->createMock(AddressAdapter::class);

        $paypalAddressBuilder = new PaypalAddressBuilder($this->mockAddress, $this->mockAddressRepository, $this->mockCountry, $this->mockAddressAdapyer);
        $result = $paypalAddressBuilder->createAddress('28');
        $this->assertEquals(true, $result);
    }
}
