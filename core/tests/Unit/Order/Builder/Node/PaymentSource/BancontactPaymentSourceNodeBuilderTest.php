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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\BancontactPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class BancontactPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(): BancontactPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn('My Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('BE');

        return new BancontactPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(): array
    {
        $address = new \stdClass();
        $address->firstname = 'Luc';
        $address->lastname = 'Dupont';
        $address->id_country = 1;

        return ['addresses' => ['invoice' => $address]];
    }

    public function testBuildReturnsCorrectStructure(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame([
            'payment_source' => [
                'bancontact' => [
                    'name' => 'Luc Dupont',
                    'country_code' => 'BE',
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
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn(str_repeat('D', 200));

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('BE');

        $builder = new BancontactPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
        $result = $builder->setCart($this->makeCart())->build();

        $this->assertSame(127, mb_strlen($result['payment_source']['bancontact']['experience_context']['brand_name']));
    }

    public function testMissingCountryIdProducesEmptyCountryCode(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Luc';
        $address->lastname = 'Dupont';

        $result = $this->makeBuilder()->setCart(['addresses' => ['invoice' => $address]])->build();

        $this->assertSame('', $result['payment_source']['bancontact']['country_code']);
    }
}
