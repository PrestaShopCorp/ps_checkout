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
use Psr\Log\LoggerInterface;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayContactNodeBuilder;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;

class ApplePayContactNodeBuilderTest extends TestCase
{
    /**
     * @param array<string, mixed> $portableAddress
     */
    private function makeBuilder(
        array $portableAddress = [],
        string $email = 'customer@example.com',
        string $countryIso = 'US'
    ): ApplePayContactNodeBuilder {
        $experienceContextHelper = $this->createMock(ExperienceContextHelper::class);
        $experienceContextHelper->method('getInvoiceCountryCode')->willReturn($countryIso);
        $experienceContextHelper->method('getCustomerEmail')->willReturn($email);
        $experienceContextHelper->method('buildInvoicePortableAddress')->willReturn($portableAddress);

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryIso);

        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->method('getNameById')->willReturn('');
        $stateRepository->method('getIsoById')->willReturn('');

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn(null);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturnCallback(
            static function (string $email): bool {
                return (bool) preg_match(Validate::PAYPAL_EMAIL_PATTERN, $email);
            }
        );

        return new ApplePayContactNodeBuilder(
            $experienceContextHelper,
            $countryRepository,
            new PaypalStateNameResolver($stateRepository),
            $phoneParser,
            $validate,
            $this->createMock(LoggerInterface::class)
        );
    }

    private function makeAddress(
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $address1 = '123 Main St',
        string $address2 = '',
        string $city = 'New York',
        string $postcode = '10001',
        int $idCountry = 1,
        int $idState = 0
    ): \stdClass {
        $address = new \stdClass();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->address1 = $address1;
        $address->address2 = $address2;
        $address->city = $city;
        $address->postcode = $postcode;
        $address->id_country = $idCountry;
        $address->id_state = $idState;
        $address->phone = '';
        $address->phone_mobile = '';
        $address->id = 1;

        return $address;
    }

    /**
     * @param array<string, mixed> $cart
     */
    private function makeContext(array $cart, bool $isVirtual = false): CheckoutContext
    {
        /** @var array<string, mixed> $cartData */
        $cartData = $cart['cart'] ?? [];
        $cartData['is_virtual'] = $isVirtual;
        $cart['cart'] = $cartData;

        return new CheckoutContext(
            $cart,
            'applepay',
            false,
            null,
            null,
            false,
            false
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(?\stdClass $invoiceAddress = null, ?\stdClass $shippingAddress = null): array
    {
        $customer = new \stdClass();
        $customer->email = 'customer@example.com';

        $addresses = [];
        if ($invoiceAddress !== null) {
            $addresses['invoice'] = $invoiceAddress;
        }
        if ($shippingAddress !== null) {
            $addresses['shipping'] = $shippingAddress;
        }

        return [
            'cart' => ['id' => 1, 'is_virtual' => false],
            'currency' => ['iso_code' => 'EUR'],
            'customer' => $customer,
            'addresses' => $addresses,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function makeDefaultPortableAddress(): array
    {
        return [
            'address_line_1' => '123 Main St',
            'admin_area_2' => 'New York',
            'country_code' => 'US',
            'postal_code' => '10001',
        ];
    }

    public function testAlwaysReturnsRequiredContactFields(): void
    {
        $cart = $this->makeCart($this->makeAddress());
        $result = $this->makeBuilder($this->makeDefaultPortableAddress())->build($this->makeContext($cart));

        $this->assertSame(['name', 'email'], $result['required_billing_contact_fields']);
        $this->assertSame(['name', 'postalAddress'], $result['required_shipping_contact_fields']);
        $this->assertSame('enabled', $result['shipping_contact_editing_mode']);
    }

    public function testVirtualCartHasEmptyShippingContactFields(): void
    {
        $cart = $this->makeCart($this->makeAddress());
        $result = $this->makeBuilder($this->makeDefaultPortableAddress())->build($this->makeContext($cart, true));

        $this->assertSame([], $result['required_shipping_contact_fields']);
    }

    public function testBillingContactPopulatedFromInvoiceAddress(): void
    {
        $cart = $this->makeCart($this->makeAddress());
        $portableAddress = $this->makeDefaultPortableAddress();

        $result = $this->makeBuilder($portableAddress)->build($this->makeContext($cart));

        $this->assertArrayHasKey('billing_contact', $result);
        /** @var array<string, mixed> $billingContact */
        $billingContact = $result['billing_contact'];
        $this->assertSame('John', $billingContact['given_name']);
        $this->assertSame('Doe', $billingContact['family_name']);
        $this->assertSame('customer@example.com', $billingContact['email_address']);
        $this->assertSame(['123 Main St'], $billingContact['address_lines']);
        $this->assertSame('New York', $billingContact['locality']);
        $this->assertSame('10001', $billingContact['postal_code']);
        $this->assertSame('US', $billingContact['country_code']);
    }

    public function testBillingContactAddress2IncludedWhenPresent(): void
    {
        $portableAddress = [
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'admin_area_2' => 'New York',
            'country_code' => 'US',
            'postal_code' => '10001',
        ];
        $cart = $this->makeCart($this->makeAddress());

        $result = $this->makeBuilder($portableAddress)->build($this->makeContext($cart));

        /** @var array<string, mixed> $billingContact */
        $billingContact = $result['billing_contact'];
        $this->assertSame(['123 Main St', 'Apt 4B'], $billingContact['address_lines']);
    }

    public function testBillingContactOmittedWhenNoInvoiceAddress(): void
    {
        $cart = $this->makeCart(null);
        $result = $this->makeBuilder()->build($this->makeContext($cart));

        $this->assertArrayNotHasKey('billing_contact', $result);
    }

    public function testBillingContactOmittedWhenNameEmpty(): void
    {
        $cart = $this->makeCart($this->makeAddress('', ''));
        $result = $this->makeBuilder($this->makeDefaultPortableAddress())->build($this->makeContext($cart));

        $this->assertArrayNotHasKey('billing_contact', $result);
    }

    public function testBillingContactOmittedWhenPortableAddressEmpty(): void
    {
        $cart = $this->makeCart($this->makeAddress());
        $result = $this->makeBuilder([])->build($this->makeContext($cart));

        $this->assertArrayNotHasKey('billing_contact', $result);
    }

    public function testInvalidEmailOmittedFromBillingContact(): void
    {
        $cart = $this->makeCart($this->makeAddress());

        $result = $this->makeBuilder($this->makeDefaultPortableAddress(), 'not-an-email')->build($this->makeContext($cart));

        /** @var array<string, mixed> $billingContact */
        $billingContact = $result['billing_contact'] ?? [];
        $this->assertArrayNotHasKey('email_address', $billingContact);
    }

    public function testShippingContactOmittedForVirtualCart(): void
    {
        $cart = $this->makeCart($this->makeAddress(), $this->makeAddress('Jane', 'Smith'));
        $result = $this->makeBuilder($this->makeDefaultPortableAddress())->build($this->makeContext($cart, true));

        $this->assertArrayNotHasKey('shipping_contact', $result);
    }

    public function testShippingContactPopulatedWhenPresent(): void
    {
        $cart = $this->makeCart($this->makeAddress(), $this->makeAddress('Jane', 'Smith'));
        $result = $this->makeBuilder($this->makeDefaultPortableAddress())->build($this->makeContext($cart, false));

        $this->assertArrayHasKey('shipping_contact', $result);
        /** @var array<string, mixed> $shippingContact */
        $shippingContact = $result['shipping_contact'];
        $this->assertSame('Jane', $shippingContact['given_name']);
        $this->assertSame('Smith', $shippingContact['family_name']);
        $this->assertSame('US', $shippingContact['country_code']);
    }
}
