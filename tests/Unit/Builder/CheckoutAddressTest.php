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
use PrestaShop\Module\PrestashopCheckout\Builder\Address\OrderAddressBuilder;

class CheckoutAddressTest extends TestCase
{
    public function testCheckoutAddressFormat()
    {
        $stringFormat = ' ';
        $address = new CheckoutAddress($this->addresProvider());
        $street = $address->formatAddressLine($address->getField('address_line_1'));
        $city = $address->formatAddressLine($address->getField('admin_area_1'));

        $this->assertStringEndsNotWith($stringFormat, $street, 'String ends with space');
        $this->assertStringStartsNotWith($stringFormat, $street, 'String starts with space');
        $this->assertStringEndsNotWith($stringFormat, $city, 'String ends with space');
        $this->assertStringStartsNotWith($stringFormat, $city, 'String starts with space');
    }

    public function testCheckoutAddressAlias()
    {
        $address = new CheckoutAddress($this->addresProvider());
        $builder = new OrderAddressBuilder($address);
        $alias = $builder->createAddressAlias();
        // TODO: assertion
    }

    public function testGenerateCheckoutAddressCecksum()
    {
        // TODO: write test
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
        return [' K.Donelaicio  ',
            '',
            'Kaunas',
            '',
            'LT',
            '50285',
            'Jonas',
            'Pinigas',
            '+37062071053',
        ];
    }
}
