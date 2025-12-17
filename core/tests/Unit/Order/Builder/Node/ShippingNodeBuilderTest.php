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
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\Node\ShippingNodeBuilder;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\GenderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class ShippingNodeBuilderTest extends TestCase
{
    /** @var GenderRepositoryInterface|MockObject */
    private $genderRepository;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var StateRepositoryInterface|MockObject */
    private $stateRepository;

    /** @var ShippingNodeBuilder */
    private $builder;

    protected function setUp(): void
    {
        $this->genderRepository = $this->createMock(GenderRepositoryInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);

        $this->builder = new ShippingNodeBuilder(
            $this->genderRepository,
            $this->countryRepository,
            $this->stateRepository
        );
    }

    /**
     * @dataProvider provideValidCartData
     */
    public function testBuildReturnsCorrectStructureWithValidData(array $cartData, array $expectedResult): void
    {
        // Configure mocks
        $this->genderRepository
            ->expects($this->once())
            ->method('getGenderNameById')
            ->with($cartData['customer']->id_gender, $cartData['language']->id)
            ->willReturn($cartData['gender_prefix']);

        $this->countryRepository
            ->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->with($cartData['addresses']['shipping']->id_country)
            ->willReturn($cartData['country_iso']);

        $this->stateRepository
            ->expects($this->once())
            ->method('getNameById')
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
                    'state_name' => 'California',
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
                            'full_name' => 'Mr. Doe John',
                        ],
                        'address' => [
                            'address_line_1' => '123 Main St',
                            'address_line_2' => 'Apt 4B',
                            'admin_area_1' => 'California',
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
                    ],
                ],
                'expectedResult' => [
                    'shipping' => [
                        'name' => [
                            'full_name' => 'Mrs. Dubois Marie',
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
        ];
    }

    public function testSetCartReturnsSameInstance(): void
    {
        $cart = ['some' => 'data'];
        $result = $this->builder->setCart($cart);

        $this->assertSame($this->builder, $result);
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
        $this->genderRepository = null;
        $this->countryRepository = null;
        $this->stateRepository = null;
        $this->builder = null;
    }
}
