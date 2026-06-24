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
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class BancontactPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeExperienceContextHelper(string $shopName = 'My Shop', string $countryCode = 'BE'): ExperienceContextHelper
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

    private function makeBuilder(): BancontactPaymentSourceNodeBuilder
    {
        return new BancontactPaymentSourceNodeBuilder($this->makeExperienceContextHelper());
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $locale = ''): array
    {
        $address = new \stdClass();
        $address->firstname = 'Luc';
        $address->lastname = 'Dupont';
        $address->id_country = 1;

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
                'bancontact' => [
                    'name' => 'Luc Dupont',
                    'country_code' => 'BE',
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

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $builder = new BancontactPaymentSourceNodeBuilder(
            $this->makeExperienceContextHelper(str_repeat('D', 200))
        );
        $result = $builder->setCart($this->makeCart())->build();

        $this->assertSame(127, mb_strlen($result['payment_source']['bancontact']['experience_context']['brand_name']));
    }

    public function testShippingPreferenceIsGetFromFileByDefault(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame('GET_FROM_FILE', $result['payment_source']['bancontact']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsNoShippingForVirtualCart(): void
    {
        $cart = $this->makeCart();
        $cart['cart'] = ['is_virtual' => true];

        $result = $this->makeBuilder()->setCart($cart)->build();

        $this->assertSame('NO_SHIPPING', $result['payment_source']['bancontact']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsSetProvidedAddressWhenShippingAddressExists(): void
    {
        $cart = $this->makeCart();
        $shippingAddress = new \stdClass();
        $shippingAddress->id = 42;
        $cart['addresses']['shipping'] = $shippingAddress;

        $result = $this->makeBuilder()->setCart($cart)->build();

        $this->assertSame('SET_PROVIDED_ADDRESS', $result['payment_source']['bancontact']['experience_context']['shipping_preference']);
    }

    public function testLocaleIsIncludedWhenSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('nl-NL'))->build();

        $this->assertSame('nl-NL', $result['payment_source']['bancontact']['experience_context']['locale']);
    }

    public function testLocaleIsOmittedWhenNotSupported(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart('nl_NL'))->build();

        $this->assertArrayNotHasKey('locale', $result['payment_source']['bancontact']['experience_context']);
    }

    public function testLocaleIsOmittedWhenMissing(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertArrayNotHasKey('locale', $result['payment_source']['bancontact']['experience_context']);
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
