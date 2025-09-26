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
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\PayerNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class PayerNodeBuilderTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ValidateInterface|MockObject */
    private $validate;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var StateRepositoryInterface|MockObject */
    private $stateRepository;

    /** @var PayerNodeBuilder */
    private $builder;

    /** @var PhoneNumberUtil|MockObject */
    private $phoneUtil;

    /** @var PhoneNumberUtil|MockObject */
    private $originalPhoneUtil;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->validate = $this->createMock(ValidateInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->stateRepository = $this->createMock(StateRepositoryInterface::class);

        // Store the original PhoneNumberUtil instance
        $this->originalPhoneUtil = PhoneNumberUtil::getInstance();

        // Create PhoneNumberUtil mock
        $this->phoneUtil = $this->createMock(PhoneNumberUtil::class);

        // Replace the singleton instance with our mock
        $reflection = new ReflectionClass(PhoneNumberUtil::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, $this->phoneUtil);

        $this->builder = new PayerNodeBuilder(
            $this->logger,
            $this->validate,
            $this->countryRepository,
            $this->stateRepository
        );
    }

    /**
     * @dataProvider provideValidCartData
     */
    public function testBuildReturnsCorrectStructureWithValidData(array $cartData, array $expectedResult, array $mockSetup): void
    {
        // Configure validator mock
        if (isset($mockSetup['isEmailValid'])) {
            $this->validate
                ->expects($this->once())
                ->method('isEmail')
                ->with($cartData['customer']->email)
                ->willReturn($mockSetup['isEmailValid']);
        }

        // Configure country repository mock
        if (isset($cartData['addresses']['invoice']->id_country)) {
            $this->countryRepository
                ->expects($this->once())
                ->method('getCountryIsoCodeById')
                ->with($cartData['addresses']['invoice']->id_country)
                ->willReturn($mockSetup['countryIso']);
        }

        // Configure state repository mock if state is present
        if (isset($cartData['addresses']['invoice']->id_state)) {
            $this->stateRepository
                ->expects($this->once())
                ->method('getNameById')
                ->with($cartData['addresses']['invoice']->id_state)
                ->willReturn($mockSetup['stateName']);
        }

        // Mock phone number handling if phone is present
        if (!empty($cartData['addresses']['invoice']->phone)) {
            $this->mockPhoneNumberHandling(
                $cartData['addresses']['invoice']->phone,
                $mockSetup['countryIso'],
                $mockSetup['phoneValid'] ?? true
            );
        }

        $result = $this->builder
            ->setCart($cartData)
            ->build();

        $this->assertEquals($expectedResult, $result);
    }

    private function mockPhoneNumberHandling(string $phoneNumber, string $countryCode, bool $isValid): void
    {
        // Create mock PhoneNumber
        $phoneNumberMock = $this->createMock(PhoneNumber::class);
        $phoneNumberMock->method('getNationalNumber')
            ->willReturn('1234567890');

        // Configure PhoneNumberUtil mock
        $this->phoneUtil
            ->method('parse')
            ->with($phoneNumber, $countryCode)
            ->willReturn($phoneNumberMock);

        $this->phoneUtil
            ->method('isValidNumber')
            ->with($phoneNumberMock)
            ->willReturn($isValid);

        $this->phoneUtil
            ->method('getNumberType')
            ->with($phoneNumberMock)
            ->willReturn(PhoneNumberType::FIXED_LINE);
    }

    public function provideValidCartData(): array
    {
        return [
            'complete_customer_data' => [
                'cartData' => [
                    'customer' => (object) [
                        'email' => 'john.doe@example.com',
                        'birthday' => '1990-01-01',
                    ],
                    'addresses' => [
                        'invoice' => $this->createMockAddress([
                            'id_country' => 21,
                            'id_state' => 5,
                            'firstname' => 'John',
                            'lastname' => 'Doe',
                            'address1' => '123 Main St',
                            'address2' => 'Apt 4B',
                            'city' => 'Los Angeles',
                            'postcode' => '90001',
                            'phone' => '+1234567890',
                        ]),
                    ],
                    'cart' => ['id' => 123, 'is_virtual' => false],
                ],
                'expectedResult' => [
                    'payer' => [
                        'name' => [
                            'given_name' => 'John',
                            'surname' => 'Doe',
                        ],
                        'email_address' => 'john.doe@example.com',
                        'birth_date' => '1990-01-01',
                        'address' => [
                            'address_line_1' => '123 Main St',
                            'address_line_2' => 'Apt 4B',
                            'admin_area_1' => 'California',
                            'admin_area_2' => 'Los Angeles',
                            'postal_code' => '90001',
                            'country_code' => 'US',
                        ],
                        'phone' => [
                            'phone_number' => [
                                'national_number' => '1234567890',
                            ],
                            'phone_type' => 'OTHER',
                        ],
                    ],
                ],
                'mockSetup' => [
                    'isEmailValid' => true,
                    'countryIso' => 'US',
                    'stateName' => 'California',
                    'phoneValid' => true,
                ],
            ],
            'minimal_customer_data' => [
                'cartData' => [
                    'customer' => (object) [
                        'email' => 'invalid-email',
                        'birthday' => '0000-00-00',
                    ],
                    'addresses' => [
                        'invoice' => $this->createMockAddress([
                            'id_country' => 8,
                            'id_state' => 0,
                            'firstname' => 'Marie',
                            'lastname' => 'Dubois',
                            'address1' => '15 Rue de la Paix',
                            'city' => 'Paris',
                            'postcode' => '75001',
                        ]),
                    ],
                    'cart' => ['id' => 123, 'is_virtual' => false],
                ],
                'expectedResult' => [
                    'payer' => [
                        'name' => [
                            'given_name' => 'Marie',
                            'surname' => 'Dubois',
                        ],
                        'address' => [
                            'address_line_1' => '15 Rue de la Paix',
                            'admin_area_2' => 'Paris',
                            'postal_code' => '75001',
                            'country_code' => 'FR',
                        ],
                    ],
                ],
                'mockSetup' => [
                    'isEmailValid' => false,
                    'countryIso' => 'FR',
                    'stateName' => '',
                ],
            ],
        ];
    }

    public function testBuildWithMissingInvoiceAddress(): void
    {
        $cartData = ['addresses' => []];

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Invoice address is missing in the cart.');

        $result = $this->builder
            ->setCart($cartData)
            ->build();

        $this->assertEquals([], $result);
    }

    public function testSetCartReturnsSameInstance(): void
    {
        $cart = ['some' => 'data'];
        $result = $this->builder->setCart($cart);

        $this->assertSame($this->builder, $result);
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
        // Restore the original PhoneNumberUtil instance
        if ($this->originalPhoneUtil) {
            $reflection = new ReflectionClass(PhoneNumberUtil::class);
            $instanceProperty = $reflection->getProperty('instance');
            $instanceProperty->setAccessible(true);
            $instanceProperty->setValue(null, $this->originalPhoneUtil);
        }

        $this->logger = null;
        $this->validate = null;
        $this->countryRepository = null;
        $this->stateRepository = null;
        $this->builder = null;
        $this->phoneUtil = null;
        $this->originalPhoneUtil = null;
    }
}
