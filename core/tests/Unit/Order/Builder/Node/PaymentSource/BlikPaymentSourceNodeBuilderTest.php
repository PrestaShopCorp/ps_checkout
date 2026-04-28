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

namespace Tests\Unit\PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\BlikPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class BlikPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(): BlikPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn('My Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('PL');

        return new BlikPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $email = 'anna@example.com'): array
    {
        $address = new \stdClass();
        $address->firstname = 'Anna';
        $address->lastname = 'Nowak';
        $address->id_country = 1;

        $customer = new \stdClass();
        $customer->email = $email;

        return [
            'addresses' => ['invoice' => $address],
            'customer' => $customer,
        ];
    }

    public function testBuildIncludesEmailWhenPresent(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame([
            'payment_source' => [
                'blik' => [
                    'name' => 'Anna Nowak',
                    'country_code' => 'PL',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                    'email' => 'anna@example.com',
                ],
            ],
        ], $result);
    }

    public function testBuildOmitsEmailWhenCustomerEmailIsEmpty(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart(''))->build();

        $this->assertArrayNotHasKey('email', $result['payment_source']['blik']);
    }

    public function testBuildOmitsEmailWhenCustomerIsAbsent(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Anna';
        $address->lastname = 'Nowak';
        $address->id_country = 1;

        $result = $this->makeBuilder()->setCart(['addresses' => ['invoice' => $address]])->build();

        $this->assertArrayNotHasKey('email', $result['payment_source']['blik']);
    }

    public function testBuildReturnsCorrectNameAndCountryCode(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame('Anna Nowak', $result['payment_source']['blik']['name']);
        $this->assertSame('PL', $result['payment_source']['blik']['country_code']);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn(str_repeat('F', 200));

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('PL');

        $builder = new BlikPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
        $result = $builder->setCart($this->makeCart())->build();

        $this->assertSame(127, mb_strlen($result['payment_source']['blik']['experience_context']['brand_name']));
    }
}
