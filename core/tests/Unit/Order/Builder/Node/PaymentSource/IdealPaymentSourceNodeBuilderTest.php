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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\IdealPaymentSourceNodeBuilder;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class IdealPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeExperienceContextHelper(string $shopName = 'My Shop', string $countryCode = 'NL'): ExperienceContextHelper
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryCode);

        return new ExperienceContextHelper($configuration, $link, $countryRepository, $this->createMock(StateRepositoryInterface::class));
    }

    private function makeBuilder(string $shopName = 'My Shop'): IdealPaymentSourceNodeBuilder
    {
        return new IdealPaymentSourceNodeBuilder($this->makeExperienceContextHelper($shopName));
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $firstName = 'John', string $lastName = 'Doe', int $idCountry = 1, string $locale = ''): array
    {
        $address = new \stdClass();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->id_country = $idCountry;

        $cart = ['addresses' => ['invoice' => $address]];

        if ($locale !== '') {
            $language = new \stdClass();
            $language->locale = $locale;
            $cart['language'] = $language;
        }

        return $cart;
    }

    public function testBuildReturnsCorrectStructure(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame([
            'payment_source' => [
                'ideal' => [
                    'name' => 'John Doe',
                    'country_code' => 'NL',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'shipping_preference' => 'GET_FROM_FILE',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    public function testLocaleIsIncludedWhenSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('John', 'Doe', 1, 'nl-NL'))->build();

        $this->assertSame('nl-NL', $result['payment_source']['ideal']['experience_context']['locale']);
    }

    public function testLocaleIsOmittedWhenNotSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('John', 'Doe', 1, 'nl_NL'))->build();

        $this->assertArrayNotHasKey('locale', $result['payment_source']['ideal']['experience_context']);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $result = $this->makeBuilder(str_repeat('A', 150))->setCart($this->makeCart())->build();

        $this->assertSame(127, mb_strlen($result['payment_source']['ideal']['experience_context']['brand_name']));
    }

    public function testBrandNameHasControlCharactersStripped(): void
    {
        $result = $this->makeBuilder("My\nShop\r\nName")->setCart($this->makeCart())->build();

        $this->assertSame('MyShopName', $result['payment_source']['ideal']['experience_context']['brand_name']);
    }

    public function testNameIsTrimmedWhenOnlyFirstNamePresent(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('Jane', ''))->build();

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

        $builder = new IdealPaymentSourceNodeBuilder(new ExperienceContextHelper($configuration, $link, $countryRepository, $this->createMock(StateRepositoryInterface::class)));
        $result = $builder->setCart(['addresses' => ['invoice' => $address]])->build();

        $this->assertSame('NL', $result['payment_source']['ideal']['country_code']);
    }

    public function testMissingCountryIdProducesEmptyCountryCode(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Jan';
        $address->lastname = 'Bakker';

        $result = $this->makeBuilder()->setCart(['addresses' => ['invoice' => $address]])->build();

        $this->assertSame('', $result['payment_source']['ideal']['country_code']);
    }
}
