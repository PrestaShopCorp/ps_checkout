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

namespace Tests\Unit\PsCheckout\Infrastructure\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Infrastructure\Action\CreateOrUpdateAddressAction;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutAddressRepositoryInterface;

class CreateOrUpdateAddressActionTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    private $context;

    /** @var CountryInterface|MockObject */
    private $country;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var PsCheckoutAddressRepositoryInterface|MockObject */
    private $psCheckoutAddressRepository;

    /** @var CreateOrUpdateAddressAction */
    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->country = $this->createMock(CountryInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->psCheckoutAddressRepository = $this->createMock(PsCheckoutAddressRepositoryInterface::class);

        $this->action = new CreateOrUpdateAddressAction(
            $this->context,
            $this->country,
            $this->countryRepository,
            $this->psCheckoutAddressRepository
        );
    }

    private function makeShippingData(
        string $orderId = 'ORDER-1',
        ?string $countryCode = 'US',
        string $firstName = 'John',
        string $lastName = 'Doe',
        string $street = '123 Main St',
        string $street2 = '',
        string $postalCode = '10001',
        string $city = 'New York',
        string $state = 'NY',
        string $phone = '5551234567'
    ): ExpressCheckoutShippingData {
        return new ExpressCheckoutShippingData(
            $orderId,
            $firstName,
            $lastName,
            $street,
            $street2,
            $postalCode,
            $city,
            $state,
            $countryCode,
            $phone
        );
    }

    private function setUpActiveCountry(int $shopId = 1): void
    {
        $this->country->method('getIdByIsoCode')
            ->willReturnCallback(function ($code) {
                return \Country::getByIso($code) ?: 0;
            });

        $shop = $this->getMockBuilder(\Shop::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shop->id = $shopId;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('isNeedDniByCountryId')->willReturn(false);

        $customer = $this->getMockBuilder(\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->id = 1;
        $this->context->method('getCustomer')->willReturn($customer);
    }

    private function setUpCartMock(): void
    {
        $cart = $this->getMockBuilder(\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $this->context->method('getCart')->willReturn($cart);
    }

    // -------------------------------------------------------------------------
    // Guard — missing country code
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCountryCodeIsNull(): void
    {
        $shippingData = $this->makeShippingData('ORDER-1', null);

        $this->country->expects($this->never())->method('getIdByIsoCode');

        $result = $this->action->execute($shippingData);

        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenCountryCodeIsEmpty(): void
    {
        $shippingData = $this->makeShippingData('ORDER-1', '');

        $this->country->expects($this->never())->method('getIdByIsoCode');

        $result = $this->action->execute($shippingData);

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Country not found — getIdByIsoCode returns 0
    // new Country(0) skips the ObjectModel DB load because if ($id) is falsy,
    // leaving $country->id as null, which makes !$country->id true.
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCountryIdIsZero(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(0);

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));

        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenCountryIdIsFalse(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(false);

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Checksum repository is never reached when country guards fail early
    // -------------------------------------------------------------------------

    public function testChecksumLookupIsNeverCalledWhenCountryCodeIsEmpty(): void
    {
        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $this->action->execute($this->makeShippingData('ORDER-1', ''));
    }

    public function testChecksumLookupIsNeverCalledWhenCountryNotFound(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(0);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));
    }

    public function testSaveAddressIsNeverCalledWhenCountryNotFound(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(0);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));
    }

    // -------------------------------------------------------------------------
    // Country code ↔ ISO mapping
    // PaypalCountryCodeUtility maps C2 (PayPal) → CN (PS). All other codes pass
    // through unchanged. Verify the adapter receives the mapped ISO code.
    // -------------------------------------------------------------------------

    public function testPassesPayPalC2IsoCodesToCountryAdapterAsCn(): void
    {
        $this->country->expects($this->once())
            ->method('getIdByIsoCode')
            ->with('CN')
            ->willReturn(0);

        $this->action->execute($this->makeShippingData('ORDER-1', 'C2'));
    }

    /**
     * @return array<string, array{string}>
     */
    public function providePassthroughIsoCodes(): array
    {
        return [
            'US' => ['US'],
            'AR' => ['AR'],
            'GB' => ['GB'],
            'FR' => ['FR'],
            'ES' => ['ES'],
            'IT' => ['IT'],
        ];
    }

    /**
     * @dataProvider providePassthroughIsoCodes
     */
    public function testPassesOtherIsoCodesUnchangedToCountryAdapter(string $isoCode): void
    {
        $this->country->expects($this->once())
            ->method('getIdByIsoCode')
            ->with($isoCode)
            ->willReturn(0);

        $this->action->execute($this->makeShippingData('ORDER-1', $isoCode));
    }

    // -------------------------------------------------------------------------
    // State resolution: getStateId is called only when country contains_states.
    //
    // These cases use the real PrestaShop Country class (available in the Docker
    // test environment). A positive country ID returned by getIdByIsoCode causes
    // new Country($id) to load the row from the PS installation DB.
    //
    // US / AR have contains_states = true  → getStateId must be called once.
    // GB / FR / ES / IT have contains_states = false → getStateId must not be called.
    //
    // The shop mock is required because Country::__construct reads the shop ID.
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{string, bool}>
     */
    public function provideCountryStateMatrix(): array
    {
        return [
            'US — has states'      => ['US', true],
            'AR — has states'      => ['AR', true],
            'GB — no states'       => ['GB', false],
            'FR — no states'       => ['FR', false],
            'ES — no states'       => ['ES', false],
            'IT — no states'       => ['IT', false],
        ];
    }

    /**
     * @dataProvider provideCountryStateMatrix
     */
    public function testStateResolutionMatchesCountryDefinition(string $isoCode, bool $hasStates): void
    {
        $this->setUpActiveCountry();

        if ($hasStates) {
            $this->countryRepository->expects($this->once())
                ->method('getStateId');
        } else {
            $this->countryRepository->expects($this->never())
                ->method('getStateId');
        }

        // execute() may return false (address validation/save failure is fine here;
        // the assertion is on whether getStateId was called, not the return value).
        $this->action->execute($this->makeShippingData('ORDER-1', $isoCode));
    }

    // -------------------------------------------------------------------------
    // DNI required — country found but DNI is mandatory → return false
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenDniIsRequired(): void
    {
        $this->country->method('getIdByIsoCode')
            ->willReturnCallback(function ($code) {
                return \Country::getByIso($code) ?: 0;
            });

        $shop = $this->getMockBuilder(\Shop::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('isNeedDniByCountryId')->willReturn(true);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));

        $this->assertFalse($result);
    }

    public function testSaveAddressIsNeverCalledWhenDniIsRequired(): void
    {
        $this->country->method('getIdByIsoCode')
            ->willReturnCallback(function ($code) {
                return \Country::getByIso($code) ?: 0;
            });

        $shop = $this->getMockBuilder(\Shop::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('isNeedDniByCountryId')->willReturn(true);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));
    }

    // -------------------------------------------------------------------------
    // Customer ID guard — guest users (id_customer = 0) must be rejected early
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCustomerIdIsZero(): void
    {
        $this->country->method('getIdByIsoCode')
            ->willReturnCallback(function ($code) {
                return \Country::getByIso($code) ?: 0;
            });

        $shop = $this->getMockBuilder(\Shop::class)
            ->disableOriginalConstructor()
            ->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);

        $customer = $this->getMockBuilder(\Customer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customer->id = 0;
        $this->context->method('getCustomer')->willReturn($customer);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Checksum-based deduplication
    //
    // When getAddressIdByChecksumAndCustomer returns a non-zero ID, the action
    // reuses the existing address and must NOT call saveAddress (no new row
    // should be written to pscheckout_address).
    //
    // The full "new address created → saveAddress called" path requires a real
    // DB write (Address::save()) and is covered at integration level.
    // -------------------------------------------------------------------------

    public function testSaveAddressIsNeverCalledWhenChecksumMatchFound(): void
    {
        $this->setUpActiveCountry();
        $this->setUpCartMock();

        // Existing address found for this customer/checksum pair.
        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-2', 'FR'));
    }

    public function testChecksumLookupReceivesCustomerIdAndMd5String(): void
    {
        $this->setUpActiveCountry();
        $this->setUpCartMock();

        $this->psCheckoutAddressRepository
            ->expects($this->once())
            ->method('getAddressIdByChecksumAndCustomer')
            ->with(
                $this->callback(function ($checksum) {
                    return is_string($checksum) && strlen($checksum) === 32 && ctype_xdigit($checksum);
                }),
                1 // customer id set in setUpActiveCountry
            )
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-3', 'FR'));
    }

    public function testDifferentAddressDataProducesDifferentChecksum(): void
    {
        $checksums = [];

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturnCallback(function ($checksum) use (&$checksums) {
                $checksums[] = $checksum;

                return 42; // reuse path — avoids Address::save() and cart dependency
            });

        $this->setUpActiveCountry();
        $this->setUpCartMock();

        $this->action->execute($this->makeShippingData('ORDER-A', 'FR', 'John', 'Doe', '1 Rue de la Paix'));
        $this->action->execute($this->makeShippingData('ORDER-B', 'FR', 'John', 'Doe', '2 Avenue Montaigne'));

        $this->assertCount(2, $checksums);
        $this->assertNotSame($checksums[0], $checksums[1]);
    }

    public function testSameAddressDataProducesSameChecksumAcrossOrders(): void
    {
        $checksums = [];

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturnCallback(function ($checksum) use (&$checksums) {
                $checksums[] = $checksum;

                return 42; // reuse path — avoids Address::save() and cart dependency
            });

        $this->setUpActiveCountry();
        $this->setUpCartMock();

        $this->action->execute($this->makeShippingData('ORDER-X', 'FR'));
        $this->action->execute($this->makeShippingData('ORDER-Y', 'FR'));

        $this->assertCount(2, $checksums);
        $this->assertSame($checksums[0], $checksums[1]);
    }

    // -------------------------------------------------------------------------
    // With/without shipping address (wallet fallback dimension)
    //
    // ExpressCheckoutShippingData built from purchase_units[0].shipping.address
    // (present) vs payment_source.paypal.address (fallback when shipping absent).
    // The action does not know which source was used; it only cares about
    // getCountryCode(). Both paths are indistinguishable from its perspective
    // once the DTO is constructed — guard and mapping logic are identical.
    // -------------------------------------------------------------------------

    public function testBothShippingPathsReachCountryLookup(): void
    {
        $fromShippingUnit = $this->makeShippingData('ORDER-A', 'FR');
        $fromWalletFallback = $this->makeShippingData('ORDER-B', 'FR');

        $this->country->expects($this->exactly(2))
            ->method('getIdByIsoCode')
            ->with('FR')
            ->willReturn(0);

        $this->action->execute($fromShippingUnit);
        $this->action->execute($fromWalletFallback);
    }
}
