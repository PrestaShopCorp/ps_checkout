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
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\GooglePayPaymentSourceNodeBuilder;
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

class GooglePayPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeExperienceContextHelper(
        string $countryCode = 'US',
        ?StateRepositoryInterface $stateRepository = null
    ): ExperienceContextHelper {
        $configuration = $this->createMock(ConfigurationInterface::class);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryCode);

        $defaultStateRepository = $this->createMock(StateRepositoryInterface::class);
        $defaultStateRepository->method('getIsoById')->willReturn('CA');
        $defaultStateRepository->method('getNameById')->willReturn('');

        return new ExperienceContextHelper($configuration, $link, $countryRepository, new PaypalStateNameResolver($stateRepository ?? $defaultStateRepository));
    }

    private function makeBuilder(
        bool $is3dSecureEnabled = false,
        string $contingency = 'SCA_ALWAYS',
        ?PhoneParser $phoneParser = null
    ): GooglePayPaymentSourceNodeBuilder {
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

        return new GooglePayPaymentSourceNodeBuilder(
            $payPalConfig,
            $this->makeExperienceContextHelper(),
            $validate,
            $phoneParser ?? $defaultPhoneParser
        );
    }

    private function makeAddress(
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $phone = '+12025551234',
        int $idState = 0
    ): Address {
        $address = new Address();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->phone = $phone;
        $address->phone_mobile = '';
        $address->id_country = 1;
        $address->id_state = $idState;
        $address->id = 1;
        $address->address1 = '123 Main St';
        $address->address2 = '';
        $address->city = 'Washington';
        $address->postcode = '20001';

        return $address;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCartWithAddress(string $email = 'customer@example.com', ?Address $address = null): array
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

    private function makeContext(array $cart = []): CheckoutContext
    {
        return new CheckoutContext($cart, 'google_pay', false, null, null, false, false);
    }

    public function testSupportsGooglePay(): void
    {
        $builder = $this->makeBuilder();
        $this->assertTrue($builder->supports('google_pay'));
        $this->assertFalse($builder->supports('paypal'));
    }

    public function testAlwaysReturnsExperienceContext(): void
    {
        $result = $this->makeBuilder(false)->build($this->makeContext());

        $this->assertSame([
            'payment_source' => [
                'google_pay' => [
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
                        'google_pay' => [
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            '3DS enabled with SCA_ALWAYS' => [
                true, 'SCA_ALWAYS', [
                    'payment_source' => [
                        'google_pay' => [
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
                        'google_pay' => [
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
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress('customer@example.com', $this->makeAddress('Jane', 'Smith')))
        );

        $this->assertSame('Jane Smith', $result['payment_source']['google_pay']['name']);
    }

    public function testNameOmittedWhenNoCart(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey('name', $result['payment_source']['google_pay']);
    }

    public function testEmailAddressAddedFromCustomer(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress('customer@example.com'))
        );

        $this->assertSame('customer@example.com', $result['payment_source']['google_pay']['email_address']);
    }

    public function testEmailAddressOmittedWhenEmailHasNoTld(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress('einkauf@my-shop'))
        );

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['google_pay']);
    }

    public function testPhoneNumberAddedFromParsedPhone(): void
    {
        $parsedPhone = $this->createMock(PhoneNumber::class);
        $parsedPhone->method('getNationalNumber')->willReturn('2025551234');
        $parsedPhone->method('getCountryCode')->willReturn(1);

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn($parsedPhone);

        $result = $this->makeBuilder(false, 'SCA_ALWAYS', $phoneParser)->build(
            $this->makeContext($this->makeCartWithAddress())
        );

        $this->assertSame(
            ['national_number' => '2025551234', 'country_code' => '1'],
            $result['payment_source']['google_pay']['phone_number']
        );
    }

    public function testPhoneNumberOmittedWhenParserReturnsNull(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress())
        );

        $this->assertArrayNotHasKey('phone_number', $result['payment_source']['google_pay']);
    }

    public function testCardBillingAddressAddedFromInvoiceAddress(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext($this->makeCartWithAddress())
        );

        $billingAddress = $result['payment_source']['google_pay']['card']['billing_address'];
        $this->assertSame('123 Main St', $billingAddress['address_line_1']);
        $this->assertSame('Washington', $billingAddress['admin_area_2']);
        $this->assertSame('20001', $billingAddress['postal_code']);
    }

    public function testCardBillingAddressUsesIsoStateForIsoCodeCountry(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->method('getIsoById')->willReturn('DC');
        $stateRepository->expects($this->atLeastOnce())->method('getIsoById');
        $stateRepository->expects($this->never())->method('getNameById');

        $payPalConfig = $this->createMock(PayPalConfiguration::class);
        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(false);
        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn(null);

        $builder = new GooglePayPaymentSourceNodeBuilder(
            $payPalConfig,
            $this->makeExperienceContextHelper('CA', $stateRepository), // Canada uses ISO state codes
            $validate,
            $phoneParser
        );

        $result = $builder->build(
            new \PsCheckout\Core\Order\Builder\CheckoutContext($this->makeCartWithAddress(), 'google_pay', false, null, null, false, false)
        );

        $this->assertSame('DC', $result['payment_source']['google_pay']['card']['billing_address']['admin_area_1']);
    }

    public function testCardBillingAddressAbsentWhenNoCart(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey('card', $result['payment_source']['google_pay']);
    }
}
