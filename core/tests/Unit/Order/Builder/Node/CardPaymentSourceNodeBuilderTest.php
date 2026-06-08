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
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\Node\CardPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Core\Util\PhoneParser;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use Psr\Log\LoggerInterface;

class CardPaymentSourceNodeBuilderTest extends TestCase
{
    private function makePhoneNumber(): PhoneNumber
    {
        $phone = $this->createMock(PhoneNumber::class);
        $phone->method('getCountryCode')->willReturn(33);
        $phone->method('getNationalNumber')->willReturn('612345678');

        return $phone;
    }

    private function makeExperienceContextHelper(
        string $countryIso = 'FR',
        ?StateRepositoryInterface $stateRepository = null
    ): ExperienceContextHelper {
        $configuration = $this->createMock(ConfigurationInterface::class);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(function (string $action): string {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryIso);

        $defaultStateRepository = $this->createMock(StateRepositoryInterface::class);
        $defaultStateRepository->method('getNameById')->willReturn('Île-de-France');
        $defaultStateRepository->method('getIsoById')->willReturn('CA');

        return new ExperienceContextHelper($configuration, $link, $countryRepository, $stateRepository ?? $defaultStateRepository);
    }

    private function makeBuilder(
        bool $is3dSecureEnabled = false,
        string $contingency = 'SCA_ALWAYS',
        string $countryIso = 'FR',
        ?PhoneParser $phoneParser = null,
        ?ValidateInterface $validate = null,
        ?LoggerInterface $logger = null
    ): CardPaymentSourceNodeBuilder {
        $paypalConfig = $this->createMock(PayPalConfiguration::class);
        $paypalConfig->method('is3dSecureEnabled')->willReturn($is3dSecureEnabled);
        $paypalConfig->method('getCardFieldsContingencies')->willReturn($contingency);

        $defaultValidate = $this->createMock(ValidateInterface::class);
        $defaultValidate->method('isPayPalEmail')->willReturn(true);

        $defaultPhoneParser = $this->createMock(PhoneParser::class);
        $defaultPhoneParser->method('parsePhone')->willReturn($this->makePhoneNumber());
        $defaultPhoneParser->method('getPhoneType')->willReturn('OTHER');

        return new CardPaymentSourceNodeBuilder(
            $paypalConfig,
            $this->makeExperienceContextHelper($countryIso),
            $phoneParser ?? $defaultPhoneParser,
            $validate ?? $defaultValidate,
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }

    private function makeAddress(
        string $firstName = 'John',
        string $lastName = 'Doe',
        int $idCountry = 1,
        int $idState = 0,
        string $phone = '+33612345678',
        string $phoneMobile = ''
    ): Address {
        $address = new Address();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->id_country = $idCountry;
        $address->id_state = $idState;
        $address->phone = $phone;
        $address->phone_mobile = $phoneMobile;

        return $address;
    }

    private function makeCustomer(string $email = 'john.doe@example.com'): \stdClass
    {
        $customer = new \stdClass();
        $customer->email = $email;

        return $customer;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(?Address $address = null, ?\stdClass $customer = null): array
    {
        return [
            'addresses' => ['invoice' => $address ?? $this->makeAddress()],
            'customer' => $customer ?? $this->makeCustomer(),
        ];
    }

    public function testNameIsConcatenatedFirstnameAndLastname(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart($this->makeAddress('Jane', 'Smith')))
            ->build();

        $this->assertSame('Jane Smith', $result['payment_source']['card']['name']);
    }

    public function testBillingAddressPresentByDefault(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertArrayHasKey('billing_address', $result['payment_source']['card']);
    }

    public function testWithout3dSecureNoVerificationAttribute(): void
    {
        $result = $this->makeBuilder(false)->setCart($this->makeCart())->build();

        $this->assertArrayNotHasKey('verification', $result['payment_source']['card']['attributes']);
    }

    public function testWith3dSecureAddsVerificationMethod(): void
    {
        $result = $this->makeBuilder(true)->setCart($this->makeCart())->build();

        $this->assertSame('SCA_ALWAYS', $result['payment_source']['card']['attributes']['verification']['method']);
    }

    public function testVaultIdRemovesBillingAddressAndSetsVaultId(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalVaultId('vault_abc')
            ->build();

        $this->assertArrayNotHasKey('billing_address', $result['payment_source']['card']);
        $this->assertSame('vault_abc', $result['payment_source']['card']['vault_id']);
    }

    public function testVaultIdWith3dSecureKeepsVerificationAttribute(): void
    {
        $result = $this->makeBuilder(true, 'SCA_WHEN_REQUIRED')
            ->setCart($this->makeCart())
            ->setPaypalVaultId('vault_abc')
            ->build();

        $this->assertArrayNotHasKey('billing_address', $result['payment_source']['card']);
        $this->assertSame('SCA_WHEN_REQUIRED', $result['payment_source']['card']['attributes']['verification']['method']);
        $this->assertSame('vault_abc', $result['payment_source']['card']['vault_id']);
    }

    public function testCustomerIdAddsAttributesCustomer(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalCustomerId('cust_123')
            ->build();

        $this->assertSame('cust_123', $result['payment_source']['card']['attributes']['customer']['id']);
    }

    public function testSavePaymentMethodAddsVaultAttribute(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(true)
            ->build();

        $this->assertSame('ON_SUCCESS', $result['payment_source']['card']['attributes']['vault']['store_in_vault']);
    }

    public function testSavePaymentMethodFalseOmitsVaultAttribute(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('vault', $result['payment_source']['card']['attributes']);
    }

    public function testCustomerIdAndSavePaymentMethodCoexistInAttributes(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalCustomerId('cust_123')
            ->setSavePaymentMethod(true)
            ->build();

        $this->assertSame('cust_123', $result['payment_source']['card']['attributes']['customer']['id']);
        $this->assertSame('ON_SUCCESS', $result['payment_source']['card']['attributes']['vault']['store_in_vault']);
    }

    public function testStoredCredentialIsAbsentWithoutVaultOrSave(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertArrayNotHasKey('stored_credential', $result['payment_source']['card']);
    }

    public function testStoredCredentialFirstUsageWhenSavingPaymentMethod(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(true)
            ->build();

        $this->assertSame([
            'payment_initiator' => 'CUSTOMER',
            'payment_type' => 'UNSCHEDULED',
            'usage' => 'FIRST',
        ], $result['payment_source']['card']['stored_credential']);
    }

    public function testStoredCredentialSubsequentUsageWhenVaultIdSet(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalVaultId('vault_abc')
            ->build();

        $this->assertSame([
            'payment_initiator' => 'CUSTOMER',
            'payment_type' => 'UNSCHEDULED',
            'usage' => 'SUBSEQUENT',
        ], $result['payment_source']['card']['stored_credential']);
    }

    public function testStoredCredentialSubsequentTakesPriorityOverFirst(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalVaultId('vault_abc')
            ->setSavePaymentMethod(true)
            ->build();

        $this->assertSame('SUBSEQUENT', $result['payment_source']['card']['stored_credential']['usage']);
    }

    public function testExperienceContextContainsReturnAndCancelUrls(): void
    {
        $result = $this->makeBuilder()->setCart($this->makeCart())->build();

        $this->assertSame('https://example.com/validate', $result['payment_source']['card']['experience_context']['return_url']);
        $this->assertSame('https://example.com/cancel', $result['payment_source']['card']['experience_context']['cancel_url']);
    }

    public function testUsStateResolutionUsesIsoCode(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->atLeastOnce())->method('getIsoById')->with(10)->willReturn('CA');
        $stateRepository->expects($this->never())->method('getNameById');

        $defaultPhoneParser = $this->createMock(PhoneParser::class);
        $defaultPhoneParser->method('parsePhone')->willReturn($this->makePhoneNumber());

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(true);

        $builder = new CardPaymentSourceNodeBuilder(
            $this->createMock(PayPalConfiguration::class),
            $this->makeExperienceContextHelper('US', $stateRepository),
            $defaultPhoneParser,
            $validate,
            $this->createMock(LoggerInterface::class)
        );
        $result = $builder->setCart($this->makeCart($this->makeAddress('John', 'Doe', 1, 10)))->build();

        $this->assertSame('CA', $result['payment_source']['card']['billing_address']['admin_area_1']);
    }

    public function testNonUsStateResolutionUsesName(): void
    {
        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->atLeastOnce())->method('getNameById')->with(5)->willReturn('Bayern');
        $stateRepository->expects($this->never())->method('getIsoById');

        $defaultPhoneParser = $this->createMock(PhoneParser::class);
        $defaultPhoneParser->method('parsePhone')->willReturn($this->makePhoneNumber());

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(true);

        $builder = new CardPaymentSourceNodeBuilder(
            $this->createMock(PayPalConfiguration::class),
            $this->makeExperienceContextHelper('DE', $stateRepository),
            $defaultPhoneParser,
            $validate,
            $this->createMock(LoggerInterface::class)
        );
        $result = $builder->setCart($this->makeCart($this->makeAddress('Hans', 'Müller', 1, 5)))->build();

        $this->assertSame('Bayern', $result['payment_source']['card']['billing_address']['admin_area_1']);
    }

    public function testCustomerNameIsAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart($this->makeAddress('Jane', 'Smith')))
            ->build();

        $this->assertSame('Jane', $result['payment_source']['card']['attributes']['customer']['name']['given_name']);
        $this->assertSame('Smith', $result['payment_source']['card']['attributes']['customer']['name']['surname']);
    }

    public function testCustomerEmailIsAddedToAttributesCustomer(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart(null, $this->makeCustomer('test@example.com')))
            ->build();

        $this->assertSame('test@example.com', $result['payment_source']['card']['attributes']['customer']['email_address']);
    }

    public function testThrowsWhenEmailIsInvalid(): void
    {
        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(false);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID);

        $this->makeBuilder(false, 'SCA_ALWAYS', 'FR', null, $validate)
            ->setCart($this->makeCart())
            ->build();
    }

    public function testThrowsWhenEmailIsMissing(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID);

        $this->makeBuilder()
            ->setCart(['addresses' => ['invoice' => $this->makeAddress()]])
            ->build();
    }

    public function testCustomerPhoneIsAddedToAttributesCustomer(): void
    {
        $parsedPhone = $this->createMock(PhoneNumber::class);
        $parsedPhone->method('getNationalNumber')->willReturn('612345678');

        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parsePhone')->willReturn($parsedPhone);
        $phoneParser->method('getPhoneType')->willReturn('MOBILE');

        $result = $this->makeBuilder(false, 'SCA_ALWAYS', 'FR', $phoneParser)
            ->setCart($this->makeCart())
            ->build();

        $phone = $result['payment_source']['card']['attributes']['customer']['phone'];
        $this->assertSame('612345678', $phone['phone_number']['national_number']);
        $this->assertSame('MOBILE', $phone['phone_type']);
    }

    public function testThrowsWhenPhoneIsEmpty(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);

        $this->makeBuilder()
            ->setCart($this->makeCart($this->makeAddress('John', 'Doe', 1, 0, '', '')))
            ->build();
    }

    public function testThrowsWhenPhoneIsInvalid(): void
    {
        $phoneParser = $this->createMock(PhoneParser::class);
        $phoneParser->method('parsePhone')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_CUSTOMER_PHONE_INVALID);

        $this->makeBuilder(false, 'SCA_ALWAYS', 'FR', $phoneParser)
            ->setCart($this->makeCart())
            ->build();
    }

    public function testCustomerIdMergesWithNewCustomerAttributes(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart(null, $this->makeCustomer('merge@example.com')))
            ->setPaypalCustomerId('cust_456')
            ->build();

        $customer = $result['payment_source']['card']['attributes']['customer'];
        $this->assertSame('cust_456', $customer['id']);
        $this->assertSame('merge@example.com', $customer['email_address']);
        $this->assertArrayHasKey('name', $customer);
    }
}
