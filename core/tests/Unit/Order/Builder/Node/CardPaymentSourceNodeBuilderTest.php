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

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\CardPaymentSourceNodeBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class CardPaymentSourceNodeBuilderTest extends TestCase
{
    private $paypalConfiguration;

    private $countryRepository;

    private $stateRepository;

    private $cardPaymentSourceNodeBuilder;

    protected function setUp(): void
    {
        // Mock dependencies
        $this->paypalConfiguration = $this->createMock(PayPalConfiguration::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);

        $this->cardPaymentSourceNodeBuilder = new CardPaymentSourceNodeBuilder(
            $this->paypalConfiguration,
            $this->countryRepository,
            $this->stateRepository
        );
    }

    /**
     * Test that the build method returns the correct node structure when all data is valid
     */
    public function testBuildValidData()
    {
        $address = new Address();
        $address->firstname = 'John';
        $address->lastname = 'Doe';
        $address->id_country = 1;
        $address->id_state = 10;

        $cart = [
            'addresses' => [
                'invoice' => $address,
            ],
        ];

        $this->countryRepository
            ->method('getCountryIsoCodeById')
            ->willReturn('US');

        $this->stateRepository
            ->method('getNameById')
            ->willReturn('California');

        // Set cart data
        $this->cardPaymentSourceNodeBuilder->setCart($cart);

        // Set PayPal configuration mock
        $this->paypalConfiguration
            ->method('is3dSecureEnabled')
            ->willReturn(true);

        $this->paypalConfiguration
            ->method('getCardFieldsContingencies')
            ->willReturn('3DSecureMethod');

        // Build the node
        $node = $this->cardPaymentSourceNodeBuilder->build();

        $this->assertArrayHasKey('payment_source', $node);
        $this->assertArrayHasKey('card', $node['payment_source']);
        $this->assertEquals('John Doe', $node['payment_source']['card']['name']);
        $this->assertArrayHasKey('billing_address', $node['payment_source']['card']);
        $this->assertEquals('3DSecureMethod', $node['payment_source']['card']['attributes']['verification']['method']);
    }

    /**
     * Test PayPal Vault ID is set correctly
     */
    public function testPaypalVaultId()
    {
        $address = new Address();
        $address->firstname = 'John';
        $address->lastname = 'Doe';
        $address->id_country = 1;
        $address->id_state = 10;

        $cart = [
            'addresses' => [
                'invoice' => $address,
            ],
        ];

        $this->countryRepository
            ->method('getCountryIsoCodeById')
            ->willReturn('US');

        $this->stateRepository
            ->method('getNameById')
            ->willReturn('California');

        // Set cart data
        $this->cardPaymentSourceNodeBuilder->setCart($cart);

        // Set PayPal Vault ID
        $this->cardPaymentSourceNodeBuilder->setPaypalVaultId('vault123');

        // Build the node
        $node = $this->cardPaymentSourceNodeBuilder->build();

        $this->assertArrayHasKey('vault_id', $node['payment_source']['card']);
        $this->assertEquals('vault123', $node['payment_source']['card']['vault_id']);
        $this->assertArrayNotHasKey('billing_address', $node['payment_source']['card']);
    }

    /**
     * Test that PayPal customer ID is added correctly
     */
    public function testPaypalCustomerId()
    {
        $address = new Address();
        $address->firstname = 'John';
        $address->lastname = 'Doe';
        $address->id_country = 1;
        $address->id_state = 10;

        $cart = [
            'addresses' => [
                'invoice' => $address,
            ],
        ];

        $this->countryRepository
            ->method('getCountryIsoCodeById')
            ->willReturn('US');

        $this->stateRepository
            ->method('getNameById')
            ->willReturn('California');

        // Set cart data
        $this->cardPaymentSourceNodeBuilder->setCart($cart);

        // Set PayPal customer ID
        $this->cardPaymentSourceNodeBuilder->setPaypalCustomerId('customer123');

        // Build the node
        $node = $this->cardPaymentSourceNodeBuilder->build();

        $this->assertArrayHasKey('attributes', $node['payment_source']['card']);
        $this->assertArrayHasKey('customer', $node['payment_source']['card']['attributes']);
        $this->assertEquals('customer123', $node['payment_source']['card']['attributes']['customer']['id']);
    }

    /**
     * Test that the payment method is saved if the flag is true
     */
    public function testSavePaymentMethod()
    {
        $address = new Address();
        $address->firstname = 'John';
        $address->lastname = 'Doe';
        $address->id_country = 1;
        $address->id_state = 10;

        $cart = [
            'addresses' => [
                'invoice' => $address,
            ],
        ];

        $this->countryRepository
            ->method('getCountryIsoCodeById')
            ->willReturn('US');

        $this->stateRepository
            ->method('getNameById')
            ->willReturn('California');

        // Set cart data
        $this->cardPaymentSourceNodeBuilder->setCart($cart);

        // Set save payment method flag
        $this->cardPaymentSourceNodeBuilder->setSavePaymentMethod(true);

        // Build the node
        $node = $this->cardPaymentSourceNodeBuilder->build();

        $this->assertArrayHasKey('vault', $node['payment_source']['card']['attributes']);
        $this->assertEquals('ON_SUCCESS', $node['payment_source']['card']['attributes']['vault']['store_in_vault']);
    }
}
