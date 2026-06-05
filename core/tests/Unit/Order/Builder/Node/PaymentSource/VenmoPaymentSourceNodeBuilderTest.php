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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\VenmoPaymentSourceNodeBuilder;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class VenmoPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(string $shopName = 'Test Shop', ?PhoneParser $phoneParser = null): VenmoPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturnCallback(
            static function (string $email): bool {
                return (bool) preg_match(Validate::PAYPAL_EMAIL_PATTERN, $email);
            }
        );

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('US');

        $defaultPhoneParser = $this->createMock(PhoneParser::class);
        $defaultPhoneParser->method('parseFromAddress')->willReturn(null);

        return new VenmoPaymentSourceNodeBuilder(
            $configuration,
            $validate,
            $phoneParser ?? $defaultPhoneParser,
            $countryRepository
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
        $cart = $this->makeCart($email);
        $cart['addresses']['invoice'] = $address ?? $this->makeAddress();

        return $cart;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $email = 'customer@example.com', bool $isVirtual = false, bool $hasShipping = false): array
    {
        $customer = new \stdClass();
        $customer->email = $email;

        $cart = ['customer' => $customer, 'cart' => ['is_virtual' => $isVirtual]];

        if ($hasShipping) {
            $address = new \stdClass();
            $address->id = 1;
            $cart['addresses']['shipping'] = $address;
        }

        return $cart;
    }

    /**
     * @dataProvider buildDataProvider
     * @param array<string, mixed> $expected
     */
    public function testBuild(
        ?string $vaultId,
        ?string $customerId,
        bool $savePaymentMethod,
        array $expected
    ): void {
        $builder = $this->makeBuilder();
        $builder->setCart($this->makeCart());

        if ($vaultId !== null) {
            $builder->setPaypalVaultId($vaultId);
        }
        if ($customerId !== null) {
            $builder->setPaypalCustomerId($customerId);
        }
        $builder->setSavePaymentMethod($savePaymentMethod);

        $this->assertSame($expected, $builder->build());
    }

    /**
     * @return array<string, array{?string, ?string, bool, array<string, mixed>}>
     */
    public function buildDataProvider(): array
    {
        $vaultAttributes = [
            'store_in_vault' => 'ON_SUCCESS',
            'usage_pattern' => 'IMMEDIATE',
            'usage_type' => 'MERCHANT',
            'customer_type' => 'CONSUMER',
            'permit_multiple_payment_tokens' => false,
        ];

        $experienceContext = [
            'brand_name' => 'Test Shop',
            'shipping_preference' => 'GET_FROM_FILE',
            'user_action' => 'PAY_NOW',
        ];

        return [
            'no save, no vault id, no customer id' => [
                null, null, false,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method without customer id' => [
                null, null, true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method with customer id' => [
                null, 'cust_abc', true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'customer' => ['id' => 'cust_abc'],
                                'vault' => $vaultAttributes,
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'vault id without save payment method' => [
                'vault_xyz', null, false,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method with vault id' => [
                'vault_xyz', null, true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                            ],
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'all options set' => [
                'vault_xyz', 'cust_abc', true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'customer' => ['id' => 'cust_abc'],
                                'vault' => $vaultAttributes,
                            ],
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testShippingPreferenceIsNoShippingForVirtualCart(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart('customer@example.com', true))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('NO_SHIPPING', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsSetProvidedAddressWhenShippingExists(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart('customer@example.com', false, true))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('SET_PROVIDED_ADDRESS', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsGetFromFileByDefault(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('GET_FROM_FILE', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testUserActionIsPayNowWhenCartIsSet(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('PAY_NOW', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueForExpressCheckout(): void
    {
        $result = $this->makeBuilder()
            ->setIsExpressCheckout(true)
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueWhenNoCart(): void
    {
        $result = $this->makeBuilder()
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testBrandNameIsNormalizedFromShopName(): void
    {
        $result = $this->makeBuilder("My\nShop")
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('MyShop', $result['payment_source']['venmo']['experience_context']['brand_name']);
    }

    public function testEmailOmittedWhenNotValidEmailFormat(): void
    {
        $customer = new \stdClass();
        $customer->email = 42; // non-string, not a valid email when cast

        $result = $this->makeBuilder()
            ->setCart(['customer' => $customer, 'cart' => ['is_virtual' => false]])
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testEmailAddressOmittedWhenNoCart(): void
    {
        $result = $this->makeBuilder()
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testEmailAddressOmittedWhenEmailHasNoTld(): void
    {
        // Regression: emails without a TLD (e.g. einkauf@my-shop) were accepted by
        // PrestaShop's isEmail() but rejected by PayPal's API with INVALID_PARAMETER_SYNTAX.
        $result = $this->makeBuilder()
            ->setCart($this->makeCart('einkauf@my-shop'))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testCustomerIdIsIgnoredWhenSavePaymentMethodIsFalse(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalCustomerId('cust_abc')
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['venmo']);
    }

    public function testCustomerNameAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCartWithAddress('customer@example.com', $this->makeAddress('Jane', 'Smith')))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('Jane', $result['payment_source']['venmo']['attributes']['customer']['name']['given_name']);
        $this->assertSame('Smith', $result['payment_source']['venmo']['attributes']['customer']['name']['surname']);
    }

    public function testCustomerEmailAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCartWithAddress('customer@example.com'))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('customer@example.com', $result['payment_source']['venmo']['attributes']['customer']['email_address']);
    }

    public function testCustomerPhoneAddedToAttributesCustomer(): void
    {
        $parsedPhone = $this->createMock(PhoneNumber::class);
        $parsedPhone->method('getNationalNumber')->willReturn('2025551234');

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parseFromAddress')->willReturn($parsedPhone);
        $phoneParser->method('getPhoneType')->willReturn('MOBILE');

        $result = $this->makeBuilder('Test Shop', $phoneParser)
            ->setCart($this->makeCartWithAddress())
            ->setSavePaymentMethod(false)
            ->build();

        $phone = $result['payment_source']['venmo']['attributes']['customer']['phone'];
        $this->assertSame('2025551234', $phone['phone_number']['national_number']);
        $this->assertSame('MOBILE', $phone['phone_type']);
    }

    public function testCustomerPhoneOmittedWhenParserReturnsNull(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCartWithAddress())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('phone', $result['payment_source']['venmo']['attributes']['customer']);
    }

    public function testCustomerIdMergesWithCustomerAttributes(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCartWithAddress('customer@example.com'))
            ->setPaypalCustomerId('cust_abc')
            ->setSavePaymentMethod(true)
            ->build();

        $customer = $result['payment_source']['venmo']['attributes']['customer'];
        $this->assertSame('cust_abc', $customer['id']);
        $this->assertArrayHasKey('name', $customer);
        $this->assertSame('customer@example.com', $customer['email_address']);
    }

    public function testCustomerAttributesAbsentWhenNoInvoiceAddress(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['venmo']);
    }
}
