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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\EpsPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class EpsPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(): EpsPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn('My Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('AT');

        return new EpsPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $locale = ''): array
    {
        $address = new \stdClass();
        $address->firstname = 'Hans';
        $address->lastname = 'Müller';
        $address->id_country = 1;

        $cart = ['addresses' => ['invoice' => $address]];

        if ($locale !== '') {
            $language = new \stdClass();
            $language->locale = $locale;
            $cart['language'] = $language;
        }

        return $cart;
    }

    /**
     * @param array<string, mixed> $cart
     */
    private function makeContext(array $cart): CheckoutContext
    {
        return new CheckoutContext($cart, 'eps', false, null, null, false, false);
    }

    public function testSupportsEps(): void
    {
        $builder = $this->makeBuilder();

        $this->assertTrue($builder->supports('eps'));
        $this->assertFalse($builder->supports('ideal'));
    }

    public function testBuildReturnsCorrectStructure(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart()));

        $this->assertSame([
            'payment_source' => [
                'eps' => [
                    'name' => 'Hans Müller',
                    'country_code' => 'AT',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    public function testLocaleIsIncludedWhenSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('de-DE'))->build();

        $this->assertSame('de-DE', $result['payment_source']['eps']['experience_context']['locale']);
    }

    public function testLocaleIsOmittedWhenNotSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('de_DE'))->build();

        $this->assertArrayNotHasKey('locale', $result['payment_source']['eps']['experience_context']);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn(str_repeat('C', 200));

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('AT');

        $builder = new EpsPaymentSourceNodeBuilder($configuration, $link, $countryRepository);
        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame(127, mb_strlen($result['payment_source']['eps']['experience_context']['brand_name']));
    }

    public function testMissingCountryIdProducesEmptyCountryCode(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Hans';
        $address->lastname = 'Müller';

        $result = $this->makeBuilder()->build($this->makeContext(['addresses' => ['invoice' => $address]]));

        $this->assertSame('', $result['payment_source']['eps']['country_code']);
    }
}
