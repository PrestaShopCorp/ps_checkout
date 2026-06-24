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
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\CardPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class CardPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(
        bool $is3dSecureEnabled = false,
        string $contingency = 'SCA_ALWAYS',
        string $countryIso = 'FR'
    ): CardPaymentSourceNodeBuilder {
        $paypalConfig = $this->createMock(PayPalConfiguration::class);
        $paypalConfig->method('is3dSecureEnabled')->willReturn($is3dSecureEnabled);
        $paypalConfig->method('getCardFieldsContingencies')->willReturn($contingency);

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryIso);

        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->method('getNameById')->willReturn('Île-de-France');
        $stateRepository->method('getIsoById')->willReturn('CA');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(function (string $action): string {
            return 'https://example.com/' . $action;
        });

        return new CardPaymentSourceNodeBuilder($paypalConfig, $countryRepository, $stateRepository, $link);
    }

    private function makeAddress(
        string $firstName = 'John',
        string $lastName = 'Doe',
        int $idCountry = 1,
        int $idState = 0
    ): Address {
        $address = new Address();
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->id_country = $idCountry;
        $address->id_state = $idState;

        return $address;
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(?Address $address = null): array
    {
        return ['addresses' => ['invoice' => $address ?? $this->makeAddress()]];
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

    public function testWithout3dSecureNoAttributesBlock(): void
    {
        $result = $this->makeBuilder(false)->setCart($this->makeCart())->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['card']);
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

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['card']);
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
        $paypalConfig = $this->createMock(PayPalConfiguration::class);

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('US');

        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->once())->method('getIsoById')->with(10)->willReturn('CA');
        $stateRepository->expects($this->never())->method('getNameById');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/link');

        $builder = new CardPaymentSourceNodeBuilder($paypalConfig, $countryRepository, $stateRepository, $link);
        $result = $builder->setCart($this->makeCart($this->makeAddress('John', 'Doe', 1, 10)))->build();

        $this->assertSame('CA', $result['payment_source']['card']['billing_address']['admin_area_1']);
    }

    public function testNonUsStateResolutionUsesName(): void
    {
        $paypalConfig = $this->createMock(PayPalConfiguration::class);

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('DE');

        $stateRepository = $this->createMock(StateRepositoryInterface::class);
        $stateRepository->expects($this->once())->method('getNameById')->with(5)->willReturn('Bayern');
        $stateRepository->expects($this->never())->method('getIsoById');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/link');

        $builder = new CardPaymentSourceNodeBuilder($paypalConfig, $countryRepository, $stateRepository, $link);
        $result = $builder->setCart($this->makeCart($this->makeAddress('Hans', 'Müller', 1, 5)))->build();

        $this->assertSame('Bayern', $result['payment_source']['card']['billing_address']['admin_area_1']);
    }
}
