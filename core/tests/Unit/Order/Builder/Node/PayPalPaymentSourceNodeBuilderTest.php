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

namespace Tests\Unit\PsCheckout\Core\Order\Builder\Node;

use Address;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\PayPalPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class PayPalPaymentSourceNodeBuilderTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ValidateInterface|MockObject */
    private $validate;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var StateRepositoryInterface|MockObject */
    private $stateRepository;

    /** @var PhoneNumberUtil|MockObject */
    private $phoneUtil;

    /** @var PhoneNumberUtil */
    private $originalPhoneUtil;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validate = $this->createMock(ValidateInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);

        $this->originalPhoneUtil = PhoneNumberUtil::getInstance();
        $this->phoneUtil = $this->createMock(PhoneNumberUtil::class);

        $reflection = new ReflectionClass(PhoneNumberUtil::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $this->phoneUtil);
    }

    protected function tearDown(): void
    {
        if ($this->originalPhoneUtil) {
            $reflection = new ReflectionClass(PhoneNumberUtil::class);
            $instanceProperty = $reflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, $this->originalPhoneUtil);
        }
    }

    private function makeBuilder(string $shopName = 'Test Shop'): PayPalPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        return new PayPalPaymentSourceNodeBuilder(
            $configuration,
            $link,
            $this->logger,
            $this->validate,
            $this->countryRepository,
            $this->stateRepository
        );
    }

    public function testBuildReturnsCorrectBaseStructure(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame([
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'brand_name' => 'Test Shop',
                        'shipping_preference' => 'GET_FROM_FILE',
                        'contact_preference' => 'NO_CONTACT_INFO',
                        'landing_page' => 'LOGIN',
                        'payment_method_selected' => 'PAYPAL',
                        'user_action' => 'PAY_NOW',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    /**
     * @dataProvider shippingPreferenceProvider
     */
    public function testShippingPreference(bool $isVirtualCart, bool $shippingAddressExists, string $expected): void
    {
        $result = $this->makeBuilder()
            ->setVirtualCart($isVirtualCart)
            ->setShippingAddressExists($shippingAddressExists)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame($expected, $result['payment_source']['paypal']['experience_context']['shipping_preference']);
    }

    /**
     * @return array<string, array{bool, bool, string}>
     */
    public static function shippingPreferenceProvider(): array
    {
        return [
            'virtual cart → NO_SHIPPING' => [true, false, 'NO_SHIPPING'],
            'virtual cart overrides existing shipping address → NO_SHIPPING' => [true, true, 'NO_SHIPPING'],
            'physical cart with shipping address → SET_PROVIDED_ADDRESS' => [false, true, 'SET_PROVIDED_ADDRESS'],
            'physical cart without shipping address → GET_FROM_FILE' => [false, false, 'GET_FROM_FILE'],
        ];
    }

    public function testSavePaymentMethodAddsVaultBlock(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(true)
            ->build();

        $this->assertSame([
            'store_in_vault' => 'ON_SUCCESS',
            'usage_pattern' => 'IMMEDIATE',
            'usage_type' => 'MERCHANT',
            'customer_type' => 'CONSUMER',
            'permit_multiple_payment_tokens' => false,
        ], $result['payment_source']['paypal']['attributes']['vault']);
    }

    public function testSavePaymentMethodWithCustomerIdAddsCustomerBlock(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(true)
            ->setPaypalCustomerId('cust_abc')
            ->build();

        $this->assertSame('cust_abc', $result['payment_source']['paypal']['attributes']['customer']['id']);
        $this->assertArrayHasKey('vault', $result['payment_source']['paypal']['attributes']);
    }

    public function testCustomerIdWithoutSavePaymentMethodIsIgnored(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setPaypalCustomerId('cust_abc')
            ->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['paypal']);
    }

    public function testWithoutSavePaymentMethodNoAttributesBlock(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['paypal']);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $result = $this->makeBuilder(str_repeat('A', 200))
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame(127, mb_strlen($result['payment_source']['paypal']['experience_context']['brand_name']));
    }

    public function testBrandNameControlCharsAreStripped(): void
    {
        $result = $this->makeBuilder("My\nShop\r\nName")
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('MyShopName', $result['payment_source']['paypal']['experience_context']['brand_name']);
    }

    public function testWithCartAddsPayerNameAndAddress(): void
    {
        $address = $this->createMockAddress([
            'id_country' => 8,
            'id_state' => 0,
            'firstname' => 'Marie',
            'lastname' => 'Dubois',
            'address1' => '15 Rue de la Paix',
            'city' => 'Paris',
            'postcode' => '75001',
        ]);

        $this->countryRepository->method('getCountryIsoCodeById')->with(8)->willReturn('FR');
        $this->stateRepository->method('getNameById')->with(0)->willReturn('');
        $this->validate->method('isPayPalEmail')->willReturn(false);

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setCart([
                'customer' => (object) ['email' => 'invalid', 'birthday' => '0000-00-00'],
                'addresses' => ['invoice' => $address],
                'cart' => ['id' => 1, 'is_virtual' => false],
            ])
            ->build();

        $paypal = $result['payment_source']['paypal'];
        $this->assertSame(['given_name' => 'Marie', 'surname' => 'Dubois'], $paypal['name']);
        $this->assertSame('FR', $paypal['address']['country_code']);
        $this->assertArrayNotHasKey('email_address', $paypal);
        $this->assertArrayNotHasKey('birth_date', $paypal);
    }

    public function testWithCartAddsEmailAndBirthDateWhenValid(): void
    {
        $address = $this->createMockAddress([
            'id_country' => 21,
            'id_state' => 5,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'address1' => '123 Main St',
            'city' => 'Los Angeles',
            'postcode' => '90001',
        ]);

        $this->countryRepository->method('getCountryIsoCodeById')->with(21)->willReturn('US');
        $this->stateRepository->method('getIsoById')->with(5)->willReturn('CA');
        $this->validate->method('isPayPalEmail')->with('john@example.com')->willReturn(true);

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setCart([
                'customer' => (object) ['email' => 'john@example.com', 'birthday' => '1990-01-15'],
                'addresses' => ['invoice' => $address],
                'cart' => ['id' => 2, 'is_virtual' => false],
            ])
            ->build();

        $paypal = $result['payment_source']['paypal'];
        $this->assertSame('john@example.com', $paypal['email_address']);
        $this->assertSame('1990-01-15', $paypal['birth_date']);
    }

    public function testWithCartAddsValidPhone(): void
    {
        $address = $this->createMockAddress([
            'id_country' => 21,
            'id_state' => 0,
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'address1' => '1 Test Ave',
            'city' => 'New York',
            'postcode' => '10001',
            'phone' => '+12125551234',
        ]);

        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('US');
        $this->stateRepository->method('getIsoById')->willReturn('');
        $this->validate->method('isPayPalEmail')->willReturn(false);

        $phoneNumberMock = $this->createMock(PhoneNumber::class);
        $phoneNumberMock->method('getNationalNumber')->willReturn('2125551234');
        $this->phoneUtil->method('parse')->willReturn($phoneNumberMock);
        $this->phoneUtil->method('isValidNumber')->willReturn(true);
        $this->phoneUtil->method('getNumberType')->willReturn(PhoneNumberType::MOBILE);

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setCart([
                'customer' => (object) ['email' => 'x', 'birthday' => '0000-00-00'],
                'addresses' => ['invoice' => $address],
                'cart' => ['id' => 3, 'is_virtual' => false],
            ])
            ->build();

        $paypal = $result['payment_source']['paypal'];
        $this->assertSame('MOBILE', $paypal['phone']['phone_type']);
        $this->assertSame('2125551234', $paypal['phone']['phone_number']['national_number']);
    }

    public function testWithoutCartNoPayerFieldsAdded(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $paypal = $result['payment_source']['paypal'];
        $this->assertArrayNotHasKey('name', $paypal);
        $this->assertArrayNotHasKey('address', $paypal);
        $this->assertArrayNotHasKey('email_address', $paypal);
        $this->assertArrayNotHasKey('birth_date', $paypal);
        $this->assertArrayNotHasKey('phone', $paypal);
    }

    public function testMissingInvoiceAddressLogsWarningAndSkipsPayerData(): void
    {
        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Invoice address is missing in the cart.');

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setCart([
                'customer' => (object) ['email' => 'x', 'birthday' => '0000-00-00'],
                'addresses' => [],
                'cart' => ['id' => 4, 'is_virtual' => false],
            ])
            ->build();

        $paypal = $result['payment_source']['paypal'];
        $this->assertArrayNotHasKey('name', $paypal);
        $this->assertArrayNotHasKey('address', $paypal);
    }

    public function testUserActionIsPayNowWhenNotExpressCheckout(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('PAY_NOW', $result['payment_source']['paypal']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueWhenExpressCheckout(): void
    {
        $address = $this->createMockAddress(['id_country' => 1, 'id_state' => 0, 'firstname' => 'A', 'lastname' => 'B', 'address1' => '1 St', 'city' => 'City', 'postcode' => '00000']);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('FR');
        $this->stateRepository->method('getNameById')->willReturn('');
        $this->validate->method('isPayPalEmail')->willReturn(false);

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setIsExpressCheckout(true)
            ->setCart([
                'customer' => (object) ['email' => 'x', 'birthday' => '0000-00-00'],
                'addresses' => ['invoice' => $address],
                'cart' => ['id' => 99, 'is_virtual' => false],
            ])
            ->build();

        $this->assertSame('CONTINUE', $result['payment_source']['paypal']['experience_context']['user_action']);
    }

    public function testUserActionIsPayNowWithCart(): void
    {
        $address = $this->createMockAddress(['id_country' => 1, 'id_state' => 0, 'firstname' => 'A', 'lastname' => 'B', 'address1' => '1 St', 'city' => 'City', 'postcode' => '00000']);
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('FR');
        $this->stateRepository->method('getNameById')->willReturn('');
        $this->validate->method('isPayPalEmail')->willReturn(false);

        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setCart([
                'customer' => (object) ['email' => 'x', 'birthday' => '0000-00-00'],
                'addresses' => ['invoice' => $address],
                'cart' => ['id' => 99, 'is_virtual' => false],
            ])
            ->build();

        $this->assertSame('PAY_NOW', $result['payment_source']['paypal']['experience_context']['user_action']);
    }

    public function testVaultIdAppearsInOutput(): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setPaypalVaultId('vault_xyz')
            ->build();

        $this->assertSame('vault_xyz', $result['payment_source']['paypal']['vault_id']);
    }

    /**
     * @dataProvider fundingSourcePaymentMethodProvider
     */
    public function testPaymentMethodSelectedForFundingSource(string $fundingSource, string $expected): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setFundingSource($fundingSource)
            ->build();

        $this->assertSame($expected, $result['payment_source']['paypal']['experience_context']['payment_method_selected']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function fundingSourcePaymentMethodProvider(): array
    {
        return [
            'paylater → PAYPAL_PAY_LATER' => ['paylater', 'PAYPAL_PAY_LATER'],
            'credit → PAYPAL_CREDIT' => ['credit', 'PAYPAL_CREDIT'],
            'paypal → PAYPAL' => ['paypal', 'PAYPAL'],
            'unknown → PAYPAL' => ['unknown', 'PAYPAL'],
        ];
    }

    /**
     * @dataProvider contactPreferenceProvider
     */
    public function testContactPreference(bool $isExpressCheckout, string $expected): void
    {
        $result = $this->makeBuilder()
            ->setShippingAddressExists(false)
            ->setVirtualCart(false)
            ->setSavePaymentMethod(false)
            ->setIsExpressCheckout($isExpressCheckout)
            ->build();

        $this->assertSame($expected, $result['payment_source']['paypal']['experience_context']['contact_preference']);
    }

    /**
     * @return array<string, array{bool, string}>
     */
    public static function contactPreferenceProvider(): array
    {
        return [
            'express checkout → UPDATE_CONTACT_INFO' => [true, 'UPDATE_CONTACT_INFO'],
            'standard checkout → NO_CONTACT_INFO' => [false, 'NO_CONTACT_INFO'],
        ];
    }

    /**
     * @param array<string, mixed> $properties
     *
     * @return Address|MockObject
     */
    private function createMockAddress(array $properties): Address
    {
        $address = $this->createMock(Address::class);

        foreach ($properties as $property => $value) {
            $address->{$property} = $value;
        }

        return $address;
    }
}
