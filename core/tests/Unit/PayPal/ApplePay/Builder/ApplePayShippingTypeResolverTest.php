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

namespace Tests\Unit\PsCheckout\Core\PayPal\ApplePay\Builder;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayShippingTypeResolver;

class ApplePayShippingTypeResolverTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public function knownTypesProvider(): array
    {
        return [
            'SHIPPING maps to shipping'       => ['SHIPPING', 'shipping'],
            'PICKUP maps to storePickup'      => ['PICKUP', 'storePickup'],
        ];
    }

    /**
     * @dataProvider knownTypesProvider
     */
    public function testKnownTypesResolveCorrectly(string $psType, string $expected): void
    {
        $this->assertSame($expected, (new ApplePayShippingTypeResolver())->resolve($psType));
    }

    /**
     * @return array<string, array{string}>
     */
    public function unknownTypesProvider(): array
    {
        return [
            'empty string' => [''],
            'lowercase'    => ['shipping'],
            'unknown type' => ['DRONE'],
        ];
    }

    /**
     * @dataProvider unknownTypesProvider
     */
    public function testUnknownTypesReturnNull(string $psType): void
    {
        $this->assertNull((new ApplePayShippingTypeResolver())->resolve($psType));
    }
}
