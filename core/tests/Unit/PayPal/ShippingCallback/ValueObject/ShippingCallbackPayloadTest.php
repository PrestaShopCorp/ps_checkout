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

namespace Tests\Unit\PsCheckout\Core\PayPal\ShippingCallback\ValueObject;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;

class ShippingCallbackPayloadTest extends TestCase
{
    public function testParsesShippingAddressFields(): void
    {
        $data = [
            'id' => '5O190127TN364715T',
            'shipping_address' => [
                'country_code' => 'US',
                'admin_area_1' => 'TX',
                'admin_area_2' => 'Dallas',
                'postal_code' => '75001',
            ],
        ];

        $payload = new ShippingCallbackPayload($data);

        $this->assertSame('5O190127TN364715T', $payload->getPaypalOrderId());
        $this->assertSame('US', $payload->getCountryCode());
        $this->assertSame('TX', $payload->getAdminArea1());
        $this->assertSame('Dallas', $payload->getAdminArea2());
        $this->assertSame('75001', $payload->getPostalCode());
        $this->assertNull($payload->getShippingOptionId());
        $this->assertTrue($payload->isAddressEvent());
    }

    public function testParsesShippingOptionId(): void
    {
        $data = [
            'id' => '5O190127TN364715T',
            'shipping_address' => [
                'country_code' => 'US',
                'admin_area_1' => 'TX',
                'admin_area_2' => 'Dallas',
                'postal_code' => '75001',
            ],
            'shipping_option' => [
                'id' => '3',
                'amount' => ['currency_code' => 'USD', 'value' => '7.00'],
                'type' => 'SHIPPING',
                'label' => 'USPS Priority',
            ],
        ];

        $payload = new ShippingCallbackPayload($data);

        $this->assertSame('3', $payload->getShippingOptionId());
        $this->assertFalse($payload->isAddressEvent());
    }

    public function testParsesReferenceId(): void
    {
        $payload = new ShippingCallbackPayload([
            'purchase_units' => [
                ['reference_id' => 'd9f80740-38f0-11e8-b467-0ed5f89f718b'],
            ],
        ]);

        $this->assertSame('d9f80740-38f0-11e8-b467-0ed5f89f718b', $payload->getReferenceId());
    }

    public function testReferenceIdDefaultsToDefaultWhenMissing(): void
    {
        $this->assertSame('default', (new ShippingCallbackPayload([]))->getReferenceId());
        $this->assertSame('default', (new ShippingCallbackPayload(['purchase_units' => [[]]]))->getReferenceId());
    }

    public function testHandlesMissingFields(): void
    {
        $payload = new ShippingCallbackPayload([]);

        $this->assertSame('', $payload->getPaypalOrderId());
        $this->assertSame('', $payload->getCountryCode());
        $this->assertSame('', $payload->getAdminArea1());
        $this->assertSame('', $payload->getAdminArea2());
        $this->assertSame('', $payload->getPostalCode());
        $this->assertNull($payload->getShippingOptionId());
        $this->assertTrue($payload->isAddressEvent());
        $this->assertSame('default', $payload->getReferenceId());
    }

    public function testShippingOptionIdIsCastToString(): void
    {
        $payload = new ShippingCallbackPayload([
            'shipping_option' => ['id' => 42],
        ]);

        $this->assertSame('42', $payload->getShippingOptionId());
    }
}
