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
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\IdealPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class IdealPaymentSourceNodeBuilderTest extends TestCase
{
    /**
     * @return array<int, mixed>
     */
    private function makeBuilder(string $shopName = 'My Shop'): array
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('NL');

        return [new IdealPaymentSourceNodeBuilder($configuration, $link, $countryRepository), $configuration, $countryRepository];
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $firstName = 'John', string $lastName = 'Doe', int $idCountry = 1): array
    {
        $address = new \stdClass();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->id_country = $idCountry;

        return ['addresses' => ['invoice' => $address]];
    }

    /**
     * @param array<string, mixed> $cart
     */
    private function makeContext(array $cart): CheckoutContext
    {
        return new CheckoutContext($cart, 'ideal', false, null, null, false, false);
    }

    public function testSupportsIdeal(): void
    {
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder();

        $this->assertTrue($builder->supports('ideal'));
        $this->assertFalse($builder->supports('blik'));
    }

    public function testBuildReturnsCorrectStructure(): void
    {
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder();

        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame([
            'payment_source' => [
                'ideal' => [
                    'name' => 'John Doe',
                    'country_code' => 'NL',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $longName = str_repeat('A', 150);
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder($longName);

        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame(127, mb_strlen($result['payment_source']['ideal']['experience_context']['brand_name']));
    }

    public function testBrandNameHasControlCharactersStripped(): void
    {
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder("My\nShop\r\nName");

        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame('MyShopName', $result['payment_source']['ideal']['experience_context']['brand_name']);
    }

    public function testNameIsTrimmedWhenOnlyFirstNamePresent(): void
    {
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder();
        $cart = $this->makeCart('Jane', '');

        $result = $builder->build($this->makeContext($cart));

        $this->assertSame('Jane', $result['payment_source']['ideal']['name']);
    }

    public function testCountryCodeComesFromRepository(): void
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn('Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->with(42)
            ->willReturn('NL');

        $address = new \stdClass();
        $address->firstname = 'Jan';
        $address->lastname = 'Bakker';
        $address->id_country = 42;

        $builder = new IdealPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
        $result = $builder->build($this->makeContext(['addresses' => ['invoice' => $address]]));

        $this->assertSame('NL', $result['payment_source']['ideal']['country_code']);
    }

    public function testMissingCountryIdProducesEmptyCountryCode(): void
    {
        /** @var IdealPaymentSourceNodeBuilder $builder */
        [$builder] = $this->makeBuilder();

        $address = new \stdClass();
        $address->firstname = 'Jan';
        $address->lastname = 'Bakker';

        $result = $builder->build($this->makeContext(['addresses' => ['invoice' => $address]]));

        $this->assertSame('', $result['payment_source']['ideal']['country_code']);
    }
}
