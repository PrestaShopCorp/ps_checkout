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

use Address;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\Node\PuiPaymentSourceNodeBuilder;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class PuiPaymentSourceNodeBuilderTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ValidateInterface|MockObject */
    private $validate;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var LinkInterface|MockObject */
    private $link;

    /** @var TranslatorInterface|MockObject */
    private $translator;

    /** @var PhoneParser|MockObject */
    private $phoneParser;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validate = $this->createMock(ValidateInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->link = $this->createMock(LinkInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->phoneParser = $this->createMock(PhoneParser::class);
    }

    private function makeBuilder(): PuiPaymentSourceNodeBuilder
    {
        $this->configuration->method('get')->willReturnMap([
            ['PS_SHOP_PHONE', '+49 30 123456'],
            ['PS_SHOP_EMAIL', 'shop@example.com'],
        ]);
        $this->link->method('getPageLink')->willReturn('https://example.com/contact');
        $this->translator->method('trans')->willReturnArgument(0);

        return new PuiPaymentSourceNodeBuilder(
            $this->logger,
            $this->validate,
            $this->countryRepository,
            $this->configuration,
            $this->link,
            $this->translator,
            $this->phoneParser
        );
    }

    /**
     * @param array<string, mixed> $invoiceAddressProps
     *
     * @return array<string, mixed>
     */
    private function makeCart(array $invoiceAddressProps = [], string $email = 'customer@example.com'): array
    {
        $address = $this->createMock(Address::class);
        $address->id_country = 276;
        $address->firstname = 'Hans';
        $address->lastname = 'Müller';
        $address->phone = '';
        $address->phone_mobile = '';
        $address->address1 = 'Hauptstraße 1';
        $address->address2 = '';
        $address->city = 'Berlin';
        $address->postcode = '10115';

        foreach ($invoiceAddressProps as $prop => $value) {
            $address->$prop = $value;
        }

        $customer = new \stdClass();
        $customer->email = $email;

        return [
            'cart' => ['id' => 1],
            'addresses' => ['invoice' => $address],
            'customer' => $customer,
            'language' => (object) ['locale' => 'de_DE'],
        ];
    }

    private function stubValidPhone(): void
    {
        $phoneNumber = $this->createMock(PhoneNumber::class);
        $phoneNumber->method('getNationalNumber')->willReturn('1701234567');
        $phoneNumber->method('getCountryCode')->willReturn(49);

        $this->phoneParser->method('parsePhone')->willReturn($phoneNumber);
    }

    public function testBuildSuccessWithValidPhone(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $result = $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491701234567']))
            ->setBirthDate('1990-01-15')
            ->build();

        $phone = $result['payment_source']['pay_upon_invoice']['phone'];
        $this->assertSame('1701234567', $phone['national_number']);
        $this->assertSame('49', $phone['country_code']);
    }

    public function testBuildFallsBackToAddressPhone(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $result = $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491701234567']))
            ->setPhone(null)
            ->setBirthDate('1990-01-15')
            ->build();

        $this->assertArrayHasKey('phone', $result['payment_source']['pay_upon_invoice']);
    }

    public function testBuildFallsBackToAddressMobilePhone(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $result = $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '', 'phone_mobile' => '+491701234567']))
            ->setBirthDate('1990-01-15')
            ->build();

        $this->assertArrayHasKey('phone', $result['payment_source']['pay_upon_invoice']);
    }

    public function testThrowsWhenPhoneIsEmpty(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '', 'phone_mobile' => '']))
            ->build();
    }

    public function testThrowsWhenPhoneIsParsableButInvalid(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->phoneParser->method('parsePhone')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491']))
            ->build();
    }

    public function testThrowsWhenPhoneIsUnparseable(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->phoneParser->method('parsePhone')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => 'not-a-phone']))
            ->build();
    }

    public function testThrowsWhenEmailIsInvalid(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(false);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart())
            ->build();
    }

    public function testBirthDateIncludedWhenValid(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $result = $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491701234567']))
            ->setBirthDate('1990-01-15')
            ->build();

        $this->assertSame('1990-01-15', $result['payment_source']['pay_upon_invoice']['birth_date']);
    }

    public function testThrowsWhenBirthDateFormatIsInvalid(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_BIRTH_DATE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491701234567']))
            ->setBirthDate('15/01/1990')
            ->build();
    }

    public function testThrowsWhenBirthDateIsEmpty(): void
    {
        $this->validate->method('isPayPalEmail')->willReturn(true);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');
        $this->stubValidPhone();

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_BIRTH_DATE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart(['phone' => '+491701234567']))
            ->build();
    }

    public function testThrowsWhenInvoiceAddressMissing(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);

        $this->makeBuilder()
            ->setCart([
                'cart' => ['id' => 1],
                'addresses' => [],
                'customer' => (object) ['email' => 'a@b.com'],
                'language' => (object) ['locale' => 'de_DE'],
            ])
            ->build();
    }

    public function testThrowsWhenCustomerMissing(): void
    {
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('DE');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);

        $address = $this->createMock(Address::class);
        $address->id_country = 276;
        $address->firstname = 'Hans';
        $address->lastname = 'Müller';

        $this->makeBuilder()
            ->setCart([
                'cart' => ['id' => 1],
                'addresses' => ['invoice' => $address],
                'language' => (object) ['locale' => 'de_DE'],
            ])
            ->build();
    }
}
