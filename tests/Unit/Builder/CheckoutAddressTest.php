<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Tests\Unit\Builder;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\CheckoutAddress;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\CountryInterface;
use PrestaShop\Module\PrestashopCheckout\Builder\Address\PaypalAddressBuilder;

class CheckoutAddressTest extends TestCase
{
    private $mockCountry;

    public function testCheckoutAddressFormat()
    {
        $stringFormat = ' ';
        /* @var CountryInterface $mockCountry */
        $this->mockCountry = $this->createMock(CountryInterface::class);

        $address = new CheckoutAddress($this->addresProvider(), $this->mockCountry);

        $street = $address->formatAddressLine($address->getField('address1'));
        $city = $address->formatAddressLine($address->getField('city'));

        $this->assertStringEndsNotWith($stringFormat, $street, 'String ends with space');
        $this->assertStringStartsNotWith($stringFormat, $street, 'String starts with space');
        $this->assertStringEndsNotWith($stringFormat, $city, 'String ends with space');
        $this->assertStringStartsNotWith($stringFormat, $city, 'String starts with space');
    }

    public function testCheckoutAddressAlias()
    {
        /* @var CountryInterface $mockCountry */
        $this->mockCountry = $this->createMock(CountryInterface::class);
        $address = new CheckoutAddress($this->addresProvider(), $this->mockCountry);

        $builder = new PaypalAddressBuilder($address);
        $alias = $builder->createAddressAlias();
        $this->assertEquals('JoPi20582Do', $alias, 'Alias formed not correctlly');
    }

    public function testGenerateCheckoutAddressCecksumIsNotCorrect()
    {
        /* @var CountryInterface $mockCountry */
        $this->mockCountry = $this->createMock(CountryInterface::class);
        $address = new CheckoutAddress($this->addresProvider(), $this->mockCountry);

        $builder = new PaypalAddressBuilder($address);
        $checksum = $builder->generateChecksum();
        $this->assertNotEquals('JoPi20582Do', $checksum, 'Alias formed not correctlly');
    }

    public function testRetreaveChecksum()
    {
        // TODO: write test
    }

    public function testStoreChecksum()
    {
        // TODO: write test
    }

    public function addresProvider()
    {
        $address = new AddressDataProvider();

        return $address->getProvidedAddress();
    }
}
