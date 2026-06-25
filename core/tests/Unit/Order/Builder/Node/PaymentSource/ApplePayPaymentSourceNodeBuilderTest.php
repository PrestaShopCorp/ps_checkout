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

use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\ApplePayPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;

class ApplePayPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeExperienceContextHelper(string $countryCode = 'US'): ExperienceContextHelper
    {
        $configuration = $this->createMock(ConfigurationInterface::class);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryCode);

        return new ExperienceContextHelper($configuration, $link, $countryRepository, new PaypalStateNameResolver($this->createMock(StateRepositoryInterface::class)));
    }

    private function makeBuilder(
        bool $is3dSecureEnabled = false,
        string $contingency = 'SCA_ALWAYS',
        ?PhoneParser $phoneParser = null
    ): ApplePayPaymentSourceNodeBuilder {
        $payPalConfig = $this->createMock(PayPalConfiguration::class);
        $payPalConfig->method('is3dSecureEnabled')->willReturn($is3dSecureEnabled);
        $payPalConfig->method('getCardFieldsContingencies')->willReturn($contingency);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturnCallback(
            static function (string $email): bool {
                return (bool) preg_match(Validate::PAYPAL_EMAIL_PATTERN, $email);
            }
        );

        $defaultPhoneParser = $this->createMock(PhoneParser::class);
        $defaultPhoneParser->method('parseFromAddress')->willReturn(null);

        return new ApplePayPaymentSourceNodeBuilder(
            $payPalConfig,
            $this->makeExperienceContextHelper(),
            $validate,
            $phoneParser ?? $defaultPhoneParser
        );
    }

    private function makeAddress(
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $phone = '+12025551234'
    ): \stdClass {
        $address = new \stdClass();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->phone = $phone;
        $address->phone_mobile = '';
        $address->id_country = 1;
        $address->id = 1;

        return $address;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCartWithAddress(string $email = 'customer@example.com', ?\stdClass $address = null): array
    {
        $customer = new \stdClass();
        $customer->email = $email;

        return [
            'customer' => $customer,
            'cart' => ['id' => 42, 'is_virtual' => false],
            'addresses' => [
                'invoice' => $address ?? $this->makeAddress(),
            ],
        ];
    }

    private function makeContext(
        array $cart = [],
        bool $savePaymentMethod = false,
        ?string $paypalCustomerId = null,
        ?string $paypalVaultId = null
    ): CheckoutContext {
        return new CheckoutContext($cart, 'apple_pay', $savePaymentMethod, $paypalCustomerId, $paypalVaultId, false, false);
    }

    public function testSupportsApplePay(): void
    {
        $builder = $this->makeBuilder();
        $this->assertTrue($builder->supports('apple_pay'));
        $this->assertFalse($builder->supports('paypal'));
    }

    public function testAlwaysReturnsExperienceContext(): void
    {
        $result = $this->makeBuilder(false)->build($this->makeContext());

        $this->assertSame([
            'payment_source' => [
                'apple_pay' => [
                    'experience_context' => [
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    /**
     * @dataProvider buildDataProvider
     * @param array<string, mixed> $expected
     */
    public function testBuild(bool $is3dSecureEnabled, string $contingency, array $expected): void
    {
        $this->assertSame($expected, $this->makeBuilder($is3dSecureEnabled, $contingency)->build($this->makeContext()));
    }

    /**
     * @return array<string, array{bool, string, array<string, mixed>}>
     */
    public static function buildDataProvider(): array
    {
        $experienceContext = [
            'return_url' => 'https://example.com/validate',
            'cancel_url' => 'https://example.com/cancel',
        ];

        return [
            '3DS disabled returns only experience_context' => [
                false, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'apple_pay' => [
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            '3DS enabled with SCA_ALWAYS' => [
                true, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'apple_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => 'SCA_ALWAYS',
                                ],
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            '3DS enabled with SCA_WHEN_REQUIRED' => [
                true, 'SCA_WHEN_REQUIRED', [
                    'payment_source' => [
                        'apple_pay' => [
                            'attributes' => [
                                'verification' => [
                                    'method' => 'SCA_WHEN_REQUIRED',
                                ],
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testNameAddedFromInvoiceAddress(): void
    {
        $cart = $this->makeCartWithAddress('customer@example.com', $this->makeAddress('Jane', 'Smith'));
        $result = $this->makeBuilder()->build($this->makeContext($cart));

        $this->assertSame('Jane Smith', $result['payment_source']['apple_pay']['name']);
    }

    public function testNameOmittedWhenAddressMissing(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey('name', $result['payment_source']['apple_pay']);
    }

    public function testEmailAddressAddedFromCustomer(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCartWithAddress('customer@example.com')));

        $this->assertSame('customer@example.com', $result['payment_source']['apple_pay']['email_address']);
    }

    public function testEmailAddressOmittedWhenEmailHasNoTld(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCartWithAddress('einkauf@my-shop')));

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['apple_pay']);
    }

    public function testPhoneNumberAddedFromParsedPhone(): void
    {
        $parsedPhone = $this->createMock(PhoneNumber::class);
        $parsedPhone->method('getNationalNumber')->willReturn('2025551234');
        $parsedPhone->method('getCountryCode')->willReturn(1);

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn($parsedPhone);

        $result = $this->makeBuilder(false, 'SCA_ALWAYS', $phoneParser)
            ->build($this->makeContext($this->makeCartWithAddress()));

        $this->assertSame(
            ['national_number' => '2025551234', 'country_code' => '1'],
            $result['payment_source']['apple_pay']['phone_number']
        );
    }

    public function testPhoneNumberOmittedWhenParserReturnsNull(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCartWithAddress()));

        $this->assertArrayNotHasKey('phone_number', $result['payment_source']['apple_pay']);
    }

    public function testVaultIdAddedWhenSet(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext([], false, null, 'vault_xyz'));

        $this->assertSame('vault_xyz', $result['payment_source']['apple_pay']['vault_id']);
    }

    public function testCustomerNameAddedToAttributesCustomer(): void
    {
        $cart = $this->makeCartWithAddress('customer@example.com', $this->makeAddress('Jane', 'Smith'));
        $result = $this->makeBuilder()->build($this->makeContext($cart));

        $this->assertSame('Jane', $result['payment_source']['apple_pay']['attributes']['customer']['name']['given_name']);
        $this->assertSame('Smith', $result['payment_source']['apple_pay']['attributes']['customer']['name']['surname']);
    }

    public function testCustomerEmailAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCartWithAddress('customer@example.com')));

        $this->assertSame('customer@example.com', $result['payment_source']['apple_pay']['attributes']['customer']['email_address']);
    }

    public function testCustomerPhoneAddedToAttributesCustomer(): void
    {
        $parsedPhone = $this->createMock(PhoneNumber::class);
        $parsedPhone->method('getNationalNumber')->willReturn('2025551234');

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn($parsedPhone);
        $phoneParser->method('getPhoneType')->willReturn('MOBILE');

        $result = $this->makeBuilder(false, 'SCA_ALWAYS', $phoneParser)
            ->build($this->makeContext($this->makeCartWithAddress()));

        $phone = $result['payment_source']['apple_pay']['attributes']['customer']['phone'];
        $this->assertSame('2025551234', $phone['phone_number']['national_number']);
        $this->assertSame('MOBILE', $phone['phone_type']);
    }

    public function testCustomerIdAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress('customer@example.com'), false, 'cust_abc')
        );

        $this->assertSame('cust_abc', $result['payment_source']['apple_pay']['attributes']['customer']['id']);
    }

    public function testCustomerIdMergesWithCustomerAttributes(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress('customer@example.com'), true, 'cust_abc')
        );

        $customer = $result['payment_source']['apple_pay']['attributes']['customer'];
        $this->assertSame('cust_abc', $customer['id']);
        $this->assertArrayHasKey('name', $customer);
        $this->assertSame('customer@example.com', $customer['email_address']);
    }

    public function testCustomerAttributesAbsentWhenNoInvoiceAddress(): void
    {
        $customer = new \stdClass();
        $customer->email = 'customer@example.com';

        $result = $this->makeBuilder()->build(
            $this->makeContext(['customer' => $customer, 'cart' => ['is_virtual' => false]])
        );

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['apple_pay']);
    }

    public function testSavePaymentMethodAddsVaultAttribute(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext([], true));

        $this->assertSame('ON_SUCCESS', $result['payment_source']['apple_pay']['attributes']['vault']['store_in_vault']);
    }

    public function testStoredCredentialIsSubsequentWhenVaultIdSet(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext([], false, null, 'vault_xyz'));

        $this->assertSame([
            'payment_initiator' => 'CUSTOMER',
            'payment_type' => 'UNSCHEDULED',
            'usage' => 'SUBSEQUENT',
        ], $result['payment_source']['apple_pay']['stored_credential']);
    }

    public function testStoredCredentialIsFirstWhenSavePaymentMethodWithoutVaultId(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext([], true));

        $this->assertSame([
            'payment_initiator' => 'CUSTOMER',
            'payment_type' => 'UNSCHEDULED',
            'usage' => 'FIRST',
        ], $result['payment_source']['apple_pay']['stored_credential']);
    }

    public function testStoredCredentialAbsentWhenNeitherVaultNorSave(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey('stored_credential', $result['payment_source']['apple_pay']);
    }
}
