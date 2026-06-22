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
use Psr\Log\LoggerInterface;
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

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var CreateOrUpdateAddressAction */
    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->country = $this->createMock(CountryInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->psCheckoutAddressRepository = $this->createMock(PsCheckoutAddressRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->action = new CreateOrUpdateAddressAction(
            $this->context,
            $this->country,
            $this->countryRepository,
            $this->psCheckoutAddressRepository,
            $this->logger
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

    /**
     * Sets up country + shop + customer mocks.
     *
     * $customerId = 0 (default) makes the action return false at the customer guard
     * after the state resolution block, which avoids Address::save() DB calls.
     * Use $customerId = 1 for tests that need to reach the checksum/address code.
     */
    private function setUpAvailableCountry(bool $containsStates = false, int $customerId = 0): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(75);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('containsStates')->willReturn($containsStates);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);

        $shop = $this->getMockBuilder(\Shop::class)->disableOriginalConstructor()->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $customer = $this->getMockBuilder(\Customer::class)->disableOriginalConstructor()->getMock();
        $customer->id = $customerId;
        $this->context->method('getCustomer')->willReturn($customer);
    }

    private function setUpCartMock(): void
    {
        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
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
    // Country not found — getIdByIsoCode returns 0 or false
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
    // Country not available — isAvailableForDelivery returns false
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCountryIsNotAvailableForDelivery(): void
    {
        $shop = $this->getMockBuilder(\Shop::class)->disableOriginalConstructor()->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('getIdByIsoCode')->willReturn(75);
        $this->country->method('isAvailableForDelivery')->willReturn(false);

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // DNI required
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenDniIsRequired(): void
    {
        $shop = $this->getMockBuilder(\Shop::class)->disableOriginalConstructor()->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('getIdByIsoCode')->willReturn(75);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(true);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));

        $this->assertFalse($result);
    }

    public function testSaveAddressIsNeverCalledWhenDniIsRequired(): void
    {
        $shop = $this->getMockBuilder(\Shop::class)->disableOriginalConstructor()->getMock();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $this->country->method('getIdByIsoCode')->willReturn(75);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(true);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));
    }

    // -------------------------------------------------------------------------
    // Customer ID guard
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCustomerIdIsZero(): void
    {
        $this->setUpAvailableCountry(false, 0);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // State resolution
    //
    // customer id = 0 (default) → action returns false at customer guard
    // after the state resolution block, so no Address DB calls are needed.
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{string}>
     */
    public function provideIsoCodeCountriesWithStates(): array
    {
        return [
            'US — ISO code states' => ['US'],
            'AR — ISO code states' => ['AR'],
        ];
    }

    /**
     * @dataProvider provideIsoCodeCountriesWithStates
     */
    public function testStateResolutionUsesIsoCodeLookupForIsoCodeCountries(string $isoCode): void
    {
        $this->setUpAvailableCountry(true);

        $this->countryRepository->expects($this->once())
            ->method('getStateIdByIsoCode');
        $this->countryRepository->expects($this->never())
            ->method('getStateId');

        $this->action->execute($this->makeShippingData('ORDER-1', $isoCode));
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideCountriesWithoutStates(): array
    {
        return [
            'GB — no states' => ['GB'],
            'FR — no states' => ['FR'],
            'ES — no states' => ['ES'],
            'IT — no states' => ['IT'],
        ];
    }

    /**
     * @dataProvider provideCountriesWithoutStates
     */
    public function testStateResolutionIsSkippedForCountriesWithoutStates(string $isoCode): void
    {
        $this->setUpAvailableCountry(false);

        $this->countryRepository->expects($this->never())
            ->method('getStateId');
        $this->countryRepository->expects($this->never())
            ->method('getStateIdByIsoCode');

        $this->action->execute($this->makeShippingData('ORDER-1', $isoCode));
    }

    public function testStateResolutionIsSkippedWhenStateIsNull(): void
    {
        $this->setUpAvailableCountry(true);

        $this->countryRepository->expects($this->never())
            ->method('getStateId');
        $this->countryRepository->expects($this->never())
            ->method('getStateIdByIsoCode');

        $shippingData = new ExpressCheckoutShippingData('ORDER-1', 'John', 'Doe', '123 Main St', '', '10001', 'New York', null, 'US', '5551234567');
        $this->action->execute($shippingData);
    }

    // -------------------------------------------------------------------------
    // Checksum-based deduplication
    //
    // customer id = 1 allows the action to proceed to the checksum block.
    // getAddressIdByChecksumAndCustomer returns 42 (existing address found) so
    // Address::save() is never reached — no DB connection required.
    // -------------------------------------------------------------------------

    public function testSaveAddressIsNeverCalledWhenChecksumMatchFound(): void
    {
        $this->setUpAvailableCountry(false, 1);
        $this->setUpCartMock();

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-2', 'FR'));
    }

    public function testChecksumLookupReceivesCustomerIdAndMd5String(): void
    {
        $this->setUpAvailableCountry(false, 1);
        $this->setUpCartMock();

        $this->psCheckoutAddressRepository
            ->expects($this->once())
            ->method('getAddressIdByChecksumAndCustomer')
            ->with(
                $this->callback(function ($checksum) {
                    return is_string($checksum) && strlen($checksum) === 32 && ctype_xdigit($checksum);
                }),
                1 // customer id set in setUpAvailableCountry
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

                return 42;
            });

        $this->setUpAvailableCountry(false, 1);
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

                return 42;
            });

        $this->setUpAvailableCountry(false, 1);
        $this->setUpCartMock();

        $this->action->execute($this->makeShippingData('ORDER-X', 'FR'));
        $this->action->execute($this->makeShippingData('ORDER-Y', 'FR'));

        $this->assertCount(2, $checksums);
        $this->assertSame($checksums[0], $checksums[1]);
    }

    // -------------------------------------------------------------------------
    // With/without shipping address (wallet fallback dimension)
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

    // -------------------------------------------------------------------------
    // Delivery option migration
    //
    // When the cart's delivery address changes, the stored delivery_option key
    // (which encodes the address ID) must be migrated to the new address ID so
    // the carrier selected via the shipping callback remains valid.
    // -------------------------------------------------------------------------

    public function testMigratesDeliveryOptionFromOldAddressToNewAddressOnAddressChange(): void
    {
        $this->setUpAvailableCountry(false, 1);

        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id_address_delivery = 500;
        $cart->delivery_option = '{"500":"7,"}';
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $cart->expects($this->once())
            ->method('setDeliveryOption')
            ->with([42 => '7,']);
        $this->context->method('getCart')->willReturn($cart);

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));
    }

    public function testDoesNotMigrateDeliveryOptionWhenAddressUnchanged(): void
    {
        $this->setUpAvailableCountry(false, 1);

        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id_address_delivery = 42;
        $cart->delivery_option = '{"42":"7,"}';
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $cart->expects($this->never())
            ->method('setDeliveryOption');
        $this->context->method('getCart')->willReturn($cart);

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));
    }

    public function testDoesNotMigrateDeliveryOptionWhenOldAddressIsZero(): void
    {
        $this->setUpAvailableCountry(false, 1);

        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id_address_delivery = 0;
        $cart->delivery_option = null;
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $cart->expects($this->never())
            ->method('setDeliveryOption');
        $this->context->method('getCart')->willReturn($cart);

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));
    }

    public function testMigratesDeliveryOptionViaFallbackWhenKeyDoesNotMatchOldAddress(): void
    {
        // delivery_option key (999) does not match id_address_delivery (500): this happens when
        // ps_cart_product rows kept the original customer address while id_address_delivery was set
        // to the temp address. The fallback picks any key that is not the new real address (42).
        $this->setUpAvailableCountry(false, 1);

        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id_address_delivery = 500;
        $cart->delivery_option = '{"999":"3,"}';
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $cart->expects($this->once())
            ->method('setDeliveryOption')
            ->with([42 => '3,']);
        $this->context->method('getCart')->willReturn($cart);

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));
    }

    public function testDoesNotMigrateDeliveryOptionWhenOnlyEntryIsAlreadyKeyedByNewAddress(): void
    {
        $this->setUpAvailableCountry(false, 1);

        $cart = $this->getMockBuilder(\Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id_address_delivery = 500;
        $cart->delivery_option = '{"42":"3,"}';
        $cart->method('getProducts')->willReturn([]);
        $cart->method('save')->willReturn(true);
        $cart->expects($this->never())
            ->method('setDeliveryOption');
        $this->context->method('getCart')->willReturn($cart);

        $this->psCheckoutAddressRepository
            ->method('getAddressIdByChecksumAndCustomer')
            ->willReturn(42);

        $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));
    }
}
