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

namespace Tests\Unit\PsCheckout\Core\Util;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Util\ExperienceContextHelper;
use Address;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class ExperienceContextHelperTest extends TestCase
{
    private function makeHelper(
        string $shopName = 'My Shop',
        string $countryCode = 'FR',
        ?StateRepositoryInterface $stateRepository = null
    ): ExperienceContextHelper {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryCode);

        $defaultStateRepository = $this->createMock(StateRepositoryInterface::class);
        $defaultStateRepository->method('getIsoById')->willReturn('');
        $defaultStateRepository->method('getNameById')->willReturn('');

        return new ExperienceContextHelper($configuration, $link, $countryRepository, $stateRepository ?? $defaultStateRepository);
    }

    private function makeAddress(
        string $address1 = '10 Downing Street',
        string $city = 'London',
        string $postcode = 'SW1A 2AA',
        int $idState = 0,
        int $idCountry = 1
    ): Address {
        $address = new Address();
        $address->address1 = $address1;
        $address->address2 = '';
        $address->city = $city;
        $address->postcode = $postcode;
        $address->id_state = $idState;
        $address->id_country = $idCountry;

        return $address;
    }

    public function testBuildBaseContextContainsRequiredKeys(): void
    {
        $result = $this->makeHelper()->buildBaseContext([]);

        $this->assertArrayHasKey('brand_name', $result);
        $this->assertArrayHasKey('shipping_preference', $result);
        $this->assertArrayHasKey('return_url', $result);
        $this->assertArrayHasKey('cancel_url', $result);
    }

    public function testBuildBaseContextBrandName(): void
    {
        $result = $this->makeHelper('Acme')->buildBaseContext([]);

        $this->assertSame('Acme', $result['brand_name']);
    }

    public function testBuildBaseContextUrls(): void
    {
        $result = $this->makeHelper()->buildBaseContext([]);

        $this->assertSame('https://example.com/validate', $result['return_url']);
        $this->assertSame('https://example.com/cancel', $result['cancel_url']);
    }

    public function testBuildBaseContextIncludesLocaleWhenSupported(): void
    {
        $language = new \stdClass();
        $language->locale = 'fr-FR';
        $cart = ['language' => $language];

        $result = $this->makeHelper()->buildBaseContext($cart);

        $this->assertSame('fr-FR', $result['locale']);
    }

    public function testBuildBaseContextOmitsLocaleWhenNotSupported(): void
    {
        $language = new \stdClass();
        $language->locale = 'fr_FR';
        $cart = ['language' => $language];

        $result = $this->makeHelper()->buildBaseContext($cart);

        $this->assertArrayNotHasKey('locale', $result);
    }

    public function testBuildBaseContextOmitsLocaleWhenMissing(): void
    {
        $result = $this->makeHelper()->buildBaseContext([]);

        $this->assertArrayNotHasKey('locale', $result);
    }

    public function testGetInvoiceNameReturnsTrimmedFullName(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Jean';
        $address->lastname = 'Dupont';

        $result = $this->makeHelper()->getInvoiceName(['addresses' => ['invoice' => $address]]);

        $this->assertSame('Jean Dupont', $result);
    }

    public function testGetInvoiceNameTrimsWhenOnlyFirstNamePresent(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Jean';
        $address->lastname = '';

        $result = $this->makeHelper()->getInvoiceName(['addresses' => ['invoice' => $address]]);

        $this->assertSame('Jean', $result);
    }

    public function testGetInvoiceNameReturnsEmptyStringWhenAddressAbsent(): void
    {
        $result = $this->makeHelper()->getInvoiceName([]);

        $this->assertSame('', $result);
    }

    public function testGetCustomerEmailReturnsEmailWhenPresent(): void
    {
        $customer = new \stdClass();
        $customer->email = 'test@example.com';

        $result = $this->makeHelper()->getCustomerEmail(['customer' => $customer]);

        $this->assertSame('test@example.com', $result);
    }

    public function testGetCustomerEmailReturnsEmptyStringWhenCustomerAbsent(): void
    {
        $result = $this->makeHelper()->getCustomerEmail([]);

        $this->assertSame('', $result);
    }

    public function testGetInvoiceCountryCodeReturnsCodeFromRepository(): void
    {
        $address = new \stdClass();
        $address->id_country = 8;

        $result = $this->makeHelper('My Shop', 'DE')->getInvoiceCountryCode(['addresses' => ['invoice' => $address]]);

        $this->assertSame('DE', $result);
    }

    public function testGetInvoiceCountryCodeReturnsEmptyStringWhenNoIdCountry(): void
    {
        $address = new \stdClass();

        $result = $this->makeHelper()->getInvoiceCountryCode(['addresses' => ['invoice' => $address]]);

        $this->assertSame('', $result);
    }

    public function testGetInvoiceCountryCodeReturnsEmptyStringWhenNoInvoiceAddress(): void
    {
        $result = $this->makeHelper()->getInvoiceCountryCode([]);

        $this->assertSame('', $result);
    }

    public function testGetBrandNameReturnsNormalizedShopName(): void
    {
        $result = $this->makeHelper('Acme')->getBrandName();

        $this->assertSame('Acme', $result);
    }

    public function testGetBrandNameNormalizesControlChars(): void
    {
        $result = $this->makeHelper("My\nShop")->getBrandName();

        $this->assertSame('MyShop', $result);
    }

    public function testBuildUrlContextReturnsReturnAndCancelUrls(): void
    {
        $result = $this->makeHelper()->buildUrlContext();

        $this->assertSame([
            'return_url' => 'https://example.com/validate',
            'cancel_url' => 'https://example.com/cancel',
        ], $result);
    }

    public function testGetFromFileWhenCartHasNoShippingInfo(): void
    {
        $this->assertSame('GET_FROM_FILE', ExperienceContextHelper::getShippingPreference([]));
    }

    public function testNoShippingWhenCartIsVirtual(): void
    {
        $cart = ['cart' => ['is_virtual' => 1]];

        $this->assertSame('NO_SHIPPING', ExperienceContextHelper::getShippingPreference($cart));
    }

    public function testGetFromFileWhenIsVirtualIsFalse(): void
    {
        $cart = ['cart' => ['is_virtual' => 0]];

        $this->assertSame('GET_FROM_FILE', ExperienceContextHelper::getShippingPreference($cart));
    }

    public function testSetProvidedAddressWhenShippingAddressHasId(): void
    {
        $shippingAddress = new \stdClass();
        $shippingAddress->id = 7;

        $cart = ['addresses' => ['shipping' => $shippingAddress]];

        $this->assertSame('SET_PROVIDED_ADDRESS', ExperienceContextHelper::getShippingPreference($cart));
    }

    public function testGetFromFileWhenShippingAddressIdIsNull(): void
    {
        $shippingAddress = new \stdClass();
        $shippingAddress->id = null;

        $cart = ['addresses' => ['shipping' => $shippingAddress]];

        $this->assertSame('GET_FROM_FILE', ExperienceContextHelper::getShippingPreference($cart));
    }

    public function testNoShippingTakesPrecedenceOverShippingAddress(): void
    {
        $shippingAddress = new \stdClass();
        $shippingAddress->id = 7;

        $cart = [
            'cart' => ['is_virtual' => 1],
            'addresses' => ['shipping' => $shippingAddress],
        ];

        $this->assertSame('NO_SHIPPING', ExperienceContextHelper::getShippingPreference($cart));
    }

    public function testBuildInvoicePortableAddressReturnsEmptyArrayWhenNoInvoiceAddress(): void
    {
        $result = $this->makeHelper()->buildInvoicePortableAddress([]);

        $this->assertSame([], $result);
    }

    public function testBuildInvoicePortableAddressBuildsAddressFields(): void
    {
        $cart = ['addresses' => ['invoice' => $this->makeAddress('123 Main St', 'Paris', '75001', 0, 8)]];

        $result = $this->makeHelper('My Shop', 'FR')->buildInvoicePortableAddress($cart);

        $this->assertSame('123 Main St', $result['address_line_1']);
        $this->assertSame('Paris', $result['admin_area_2']);
        $this->assertSame('75001', $result['postal_code']);
        $this->assertSame('FR', $result['country_code']);
    }

    public function testBuildInvoicePortableAddressUsesIsoCodeForIsoCodeCountry(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->once())->method('getIsoById')->with(10)->willReturn('CA');
        $stateRepository->expects($this->never())->method('getNameById');

        $cart = ['addresses' => ['invoice' => $this->makeAddress('123 Main St', 'Ottawa', 'K1A 0A9', 10, 1)]];

        $result = $this->makeHelper('My Shop', 'CA', $stateRepository)->buildInvoicePortableAddress($cart);

        $this->assertSame('CA', $result['admin_area_1']);
    }

    public function testBuildInvoicePortableAddressUsesNameForNonIsoCodeCountry(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->once())->method('getNameById')->with(5)->willReturn('Bayern');
        $stateRepository->expects($this->never())->method('getIsoById');

        $cart = ['addresses' => ['invoice' => $this->makeAddress('Maximilianstr. 1', 'Munich', '80539', 5, 1)]];

        $result = $this->makeHelper('My Shop', 'DE', $stateRepository)->buildInvoicePortableAddress($cart);

        $this->assertSame('Bayern', $result['admin_area_1']);
    }

    public function testBuildInvoicePortableAddressAppliesStateCodeMapping(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->method('getIsoById')->willReturn('BCN');

        $cart = ['addresses' => ['invoice' => $this->makeAddress('Av. Principal 1', 'Tijuana', '22000', 1, 1)]];

        $result = $this->makeHelper('My Shop', 'MX', $stateRepository)->buildInvoicePortableAddress($cart);

        $this->assertSame('BC', $result['admin_area_1']);
    }
}
