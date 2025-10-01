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
use PsCheckout\Core\Order\Builder\Node\SupplementaryDataNodeBuilder;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;

class SupplementaryDataNodeBuilderTest extends TestCase
{
    private $countryRepository;

    private $stateRepository;

    protected function setUp(): void
    {
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);
    }

    /**
     * @dataProvider supplementaryDataProvider
     */
    public function testBuild(array $cart, array $payload, array $expectedOutput)
    {
        $this->countryRepository->method('getCountryIsoCodeById')->willReturn('US');
        $this->stateRepository->method('getNameById')->willReturn('California');

        $builder = new SupplementaryDataNodeBuilder($this->countryRepository, $this->stateRepository);
        $builder->setCart($cart);
        $builder->setPayload($payload);

        $result = $builder->build();

        $this->assertEquals($expectedOutput, $result);
    }

    public function supplementaryDataProvider(): array
    {
        $address1 = new Address();
        $address1->id_country = 1;
        $address1->id_state = 2;
        $address1->address1 = '123 Main St';
        $address1->address2 = 'Apt 4B';
        $address1->city = 'Los Angeles';
        $address1->postcode = '90001';

        $address2 = new Address();
        $address2->id_country = 1;
        $address2->id_state = 2;
        $address2->address1 = '';
        $address2->address2 = '';
        $address2->city = '';
        $address2->postcode = '';

        $address3 = new Address();
        $address3->id_country = 1;
        $address3->id_state = 2;
        // Missing address1, address2, city, and postcode

        return [
            'valid data' => [
                'cart' => ['addresses' => ['invoice' => $address1], 'cart' => ['is_virtual' => false]],
                'payload' => $this->getSamplePayload(),
                'expectedOutput' => $this->getExpectedOutput(true),
            ],
            'empty address fields' => [
                'cart' => ['addresses' => ['invoice' => $address2], 'cart' => ['is_virtual' => false]],
                'payload' => $this->getSamplePayload(),
                'expectedOutput' => $this->getExpectedOutput(false),
            ],
            'missing address fields' => [
                'cart' => ['addresses' => ['invoice' => $address3], 'cart' => ['is_virtual' => false]],
                'payload' => $this->getSamplePayload(),
                'expectedOutput' => $this->getExpectedOutput(false),
            ],
        ];
    }

    private function getSamplePayload(): array
    {
        return [
            'purchase_units' => [
                [
                    'amount' => [
                        'breakdown' => [
                            'tax_total' => 10.00,
                            'shipping' => 5.00,
                            'discount' => 2.00,
                        ],
                        'currency_code' => 'USD',
                        'value' => 100.00,
                    ],
                    'items' => [
                        ['item_id' => 1, 'name' => 'Product 1', 'quantity' => 2, 'price' => 50.00],
                    ],
                ],
            ],
        ];
    }

    private function getExpectedOutput(bool $hasFullAddress): array
    {
        $shippingAddress = [
            'admin_area_1' => 'California',
            'country_code' => 'US',
        ];

        if ($hasFullAddress) {
            $shippingAddress = array_merge($shippingAddress, [
                'address_line_1' => '123 Main St',
                'address_line_2' => 'Apt 4B',
                'admin_area_2' => 'Los Angeles',
                'postal_code' => '90001',
            ]);
        }

        return [
            'supplementary_data' => [
                'card' => [
                    'level_2' => [
                        'tax_total' => 10.00,
                    ],
                    'level_3' => [
                        'shipping_amount' => 5.00,
                        'duty_amount' => [
                            'currency_code' => 'USD',
                            'value' => 100.00,
                        ],
                        'discount_amount' => 2.00,
                        'shipping_address' => $shippingAddress,
                        'line_items' => [
                            ['item_id' => 1, 'name' => 'Product 1', 'quantity' => 2, 'price' => 50.00],
                        ],
                    ],
                ],
            ],
        ];
    }
}
