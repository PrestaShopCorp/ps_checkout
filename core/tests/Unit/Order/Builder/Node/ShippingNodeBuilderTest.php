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

namespace Tests\PsCheckout\Core\Order\Builder\Node;

use Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\Node\ShippingNodeBuilder;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class ShippingNodeBuilderTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var StateRepositoryInterface|MockObject */
    private $stateRepository;

    /** @var ShippingNodeBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);

        $this->builder = new ShippingNodeBuilder(
            $this->logger,
            $this->countryRepository,
            $this->stateRepository
        );
    }

    /**
     * @dataProvider provideValidCartData
     */
    public function testBuildReturnsCorrectStructureWithValidData(array $cartData, array $expectedResult): void
    {
        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->with($cartData['addresses']['shipping']->id_country)
            ->willReturn($cartData['country_iso']);

        $isoCodeCountries = ['US', 'CA', 'BR', 'IT', 'MX', 'JP', 'CN', 'C2', 'ID', 'AR'];
        $this->stateRepository
            ->expects($this->once())
            ->method(in_array($cartData['country_iso'], $isoCodeCountries, true) ? 'getIsoById' : 'getNameById')
            ->with($cartData['addresses']['shipping']->id_state)
            ->willReturn($cartData['state_name']);

        // Set cart data and build result
        $result = $this->builder
            ->setCart($cartData)
            ->build();

        $this->assertEquals($expectedResult, $result);
    }

    public function provideValidCartData(): array
    {
        return [
            'standard_customer' => [
                'cartData' => [
                    'customer' => (object) [
                        'id_gender' => 1,
                    ],
                    'language' => (object) [
                        'id' => 1,
                    ],
                    'cart' => [
                        'is_virtual' => false,
                    ],
                    'gender_prefix' => 'Mr.',
                    'country_iso' => 'US',
                    'state_name' => 'CA',
                    'addresses' => [
                        'shipping' => $this->createMockAddress([
                            'id_country' => 21,
                            'id_state' => 5,
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'address1' => '123 Main St',
                            'address2' => 'Apt 4B',
                            'city' => 'Los Angeles',
                            'postcode' => '90001',
                        ]),
                    ],
                ],
                'expectedResult' => [
                    'shipping' => [
                        'name' => [
                            'full_name' => 'John Doe',
                        ],
                        'address' => [
                            'address_line_1' => '123 Main St',
                            'address_line_2' => 'Apt 4B',
                            'admin_area_1' => 'CA',
                            'admin_area_2' => 'Los Angeles',
                            'postal_code' => '90001',
                            'country_code' => 'US',
                        ],
                    ],
                ],
            ],
            'customer_without_state' => [
                'cartData' => [
                    'customer' => (object) [
                        'id_gender' => 2,
                    ],
                    'language' => (object) [
                        'id' => 1,
                    ],
                    'gender_prefix' => 'Mrs.',
                    'country_iso' => 'FR',
                    'state_name' => '',
                    'addresses' => [
                        'shipping' => $this->createMockAddress([
                            'id_country' => 8,
                            'id_state' => 0,
                            'firstname' => 'Marie',
                            'lastname' => 'Dubois',
                            'address1' => '15 Rue de la Paix',
                            'address2' => '',
                            'city' => 'Paris',
                            'postcode' => '75001',
                        ]),
                    ],
                    'cart' => [
                        'is_virtual' => false,
                    ]
                ],
                'expectedResult' => [
                    'shipping' => [
                        'name' => [
                            'full_name' => 'Marie Dubois',
                        ],
                        'address' => [
                            'address_line_1' => '15 Rue de la Paix',
                            'admin_area_2' => 'Paris',
                            'postal_code' => '75001',
                            'country_code' => 'FR',
                        ],
                    ],
                ],
            ],
            'canadian_customer' => [
                'cartData' => [
                    'customer' => (object) [
                        'id_gender' => 1,
                    ],
                    'language' => (object) [
                        'id' => 1,
                    ],
                    'cart' => [
                        'is_virtual' => false,
                    ],
                    'gender_prefix' => 'Mr.',
                    'country_iso' => 'CA',
                    'state_name' => 'ON',
                    'addresses' => [
                        'shipping' => $this->createMockAddress([
                            'id_country' => 38,
                            'id_state' => 12,
                            'firstname' => 'Jean',
                            'lastname' => 'Tremblay',
                            'address1' => '100 King St',
                            'address2' => '',
                            'city' => 'Toronto',
                            'postcode' => 'M5H 1J8',
                        ]),
                    ],
                ],
                'expectedResult' => [
                    'shipping' => [
                        'name' => [
                            'full_name' => 'Jean Tremblay',
                        ],
                        'address' => [
                            'address_line_1' => '100 King St',
                            'admin_area_1' => 'ON',
                            'admin_area_2' => 'Toronto',
                            'postal_code' => 'M5H 1J8',
                            'country_code' => 'CA',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testSetCartReturnsSameInstance(): void
    {
        $cart = ['some' => 'data'];
        $result = $this->builder->setCart($cart);

        $this->assertSame($this->builder, $result);
    }

    /**
     * @dataProvider provideInvalidCountryCodes
     */
    public function testBuildReturnsEmptyArrayForInvalidCountryCode(string $invalidCode): void
    {
        $address = $this->createMockAddress([
            'id_country' => 99,
            'id_state' => 0,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'address1' => '1 Street',
            'address2' => '',
            'city' => 'City',
            'postcode' => '12345',
        ]);

        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->willReturn($invalidCode);

        $this->stateRepository->expects($this->never())->method($this->anything());

        $this->logger->expects($this->once())->method('warning');

        $result = $this->builder
            ->setCart([
                'cart' => ['is_virtual' => false],
                'addresses' => ['shipping' => $address],
            ])
            ->build();

        $this->assertSame([], $result);
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideInvalidCountryCodes(): array
    {
        return [
            'empty string' => [''],
            'three-letter code' => ['FRA'],
            'full country name' => ['France'],
            'numeric' => ['12'],
        ];
    }

    public function testBuildTrimsWhitespaceFromAddressFields(): void
    {
        // IE (Ireland) has optional postal code, so a blank postcode is omitted without triggering a warning
        $address = $this->createMockAddress([
            'id_country' => 29,
            'id_state' => 0,
            'firstname' => 'Sean',
            'lastname' => 'Murphy',
            'address1' => '10 Grafton Street',
            'address2' => ' ',
            'city' => ' Dublin ',
            'postcode' => ' ',
        ]);

        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->willReturn('IE');

        $this->stateRepository
            ->expects($this->once())
            ->method('getNameById')
            ->willReturn('');

        $result = $this->builder
            ->setCart([
                'cart' => ['is_virtual' => false],
                'addresses' => ['shipping' => $address],
            ])
            ->build();

        $this->assertArrayHasKey('shipping', $result);
        $this->assertSame('Dublin', $result['shipping']['address']['admin_area_2']);
        $this->assertArrayNotHasKey('postal_code', $result['shipping']['address']);
        $this->assertArrayNotHasKey('address_line_2', $result['shipping']['address']);
    }

    public function testBuildReturnsEmptyArrayWhenRequiredCityIsMissing(): void
    {
        $address = $this->createMockAddress([
            'id_country' => 8,
            'id_state' => 0,
            'firstname' => 'Marie',
            'lastname' => 'Dubois',
            'address1' => '15 Rue de la Paix',
            'address2' => '',
            'city' => '  ',
            'postcode' => '75001',
        ]);

        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->willReturn('FR');

        $this->stateRepository
            ->expects($this->once())
            ->method('getNameById')
            ->willReturn('');

        $this->logger->expects($this->once())->method('warning');

        $result = $this->builder
            ->setCart([
                'cart' => ['is_virtual' => false],
                'addresses' => ['shipping' => $address],
            ])
            ->build();

        $this->assertSame([], $result);
    }

    public function testBuildReturnsEmptyArrayWhenRequiredPostalCodeIsMissing(): void
    {
        $address = $this->createMockAddress([
            'id_country' => 21,
            'id_state' => 5,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'address1' => '123 Main St',
            'address2' => '',
            'city' => 'New York',
            'postcode' => '  ',
        ]);

        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->willReturn('US');

        $this->stateRepository
            ->expects($this->once())
            ->method('getIsoById')
            ->willReturn('NY');

        $this->logger->expects($this->once())->method('warning');

        $result = $this->builder
            ->setCart([
                'cart' => ['is_virtual' => false],
                'addresses' => ['shipping' => $address],
            ])
            ->build();

        $this->assertSame([], $result);
    }

    public function testBuildWithoutSettingCartThrowsException(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart data must be set before building order payload');

        $this->builder->build();
    }

    /**
     * Creates a mock Address object with the given properties
     *
     * @param array $properties
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

    protected function tearDown(): void
    {
        $this->countryRepository = null;
        $this->stateRepository = null;
        $this->builder = null;
    }
}
