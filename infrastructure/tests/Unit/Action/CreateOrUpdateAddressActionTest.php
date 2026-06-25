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
use PsCheckout\Infrastructure\Repository\PsCheckoutAddressRepositoryInterface;
use PsCheckout\Infrastructure\Service\CountryResolutionException;
use PsCheckout\Infrastructure\Service\PaypalAddressResolverInterface;
use PsCheckout\Infrastructure\Service\ResolvedCountryState;

class CreateOrUpdateAddressActionTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    private $context;

    /** @var PaypalAddressResolverInterface|MockObject */
    private $addressResolver;

    /** @var PsCheckoutAddressRepositoryInterface|MockObject */
    private $psCheckoutAddressRepository;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var CreateOrUpdateAddressAction */
    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->addressResolver = $this->createMock(PaypalAddressResolverInterface::class);
        $this->psCheckoutAddressRepository = $this->createMock(PsCheckoutAddressRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->action = new CreateOrUpdateAddressAction(
            $this->context,
            $this->addressResolver,
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

    private function setUpShop(int $shopId = 1): void
    {
        $shop = $this->getMockBuilder(\Shop::class)->disableOriginalConstructor()->getMock();
        $shop->id = $shopId;
        $this->context->method('getShop')->willReturn($shop);
    }

    private function setUpCustomer(int $customerId = 0): void
    {
        $customer = $this->getMockBuilder(\Customer::class)->disableOriginalConstructor()->getMock();
        $customer->id = $customerId;
        $this->context->method('getCustomer')->willReturn($customer);
    }

    /**
     * Sets up a successful country resolution and context mocks.
     *
     * $customerId = 0 (default) makes the action return false at the customer guard
     * after country resolution, which avoids Address::save() DB calls.
     * Use $customerId = 1 for tests that need to reach the checksum/address code.
     */
    private function setUpAvailableCountry(int $customerId = 0): void
    {
        $this->addressResolver->method('resolveCountryState')->willReturn(
            new ResolvedCountryState(75, 0, 'FR')
        );
        $this->setUpShop(1);
        $this->setUpCustomer($customerId);
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
        $this->addressResolver->expects($this->never())->method('resolveCountryState');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', null));

        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenCountryCodeIsEmpty(): void
    {
        $this->addressResolver->expects($this->never())->method('resolveCountryState');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', ''));

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Country not found — resolver throws COUNTRY_NOT_FOUND
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCountryNotFound(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('Country not found', CountryResolutionException::COUNTRY_NOT_FOUND, 'XX'));

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
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('Country not found', CountryResolutionException::COUNTRY_NOT_FOUND, 'XX'));

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));
    }

    public function testSaveAddressIsNeverCalledWhenCountryNotFound(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('Country not found', CountryResolutionException::COUNTRY_NOT_FOUND, 'XX'));

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-1', 'XX'));
    }

    // -------------------------------------------------------------------------
    // Country code passthrough to resolver
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{string}>
     */
    public function providePayPalCountryCodes(): array
    {
        return [
            'C2 (PayPal code for China)' => ['C2'],
            'US' => ['US'],
            'AR' => ['AR'],
            'GB' => ['GB'],
            'FR' => ['FR'],
            'ES' => ['ES'],
            'IT' => ['IT'],
        ];
    }

    /**
     * @dataProvider providePayPalCountryCodes
     */
    public function testPassesRawPayPalCountryCodeToResolver(string $paypalCode): void
    {
        $this->setUpShop(1);
        $this->addressResolver->expects($this->once())
            ->method('resolveCountryState')
            ->with($paypalCode, $this->anything(), $this->anything())
            ->willThrowException(new CountryResolutionException('not found', CountryResolutionException::COUNTRY_NOT_FOUND, $paypalCode));

        $this->action->execute($this->makeShippingData('ORDER-1', $paypalCode));
    }

    // -------------------------------------------------------------------------
    // Country not available — resolver throws COUNTRY_NOT_AVAILABLE
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCountryIsNotAvailableForDelivery(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('Not available', CountryResolutionException::COUNTRY_NOT_AVAILABLE, 'FR', 75));

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));

        $this->assertFalse($result);
    }

    public function testReturnsFalseWhenDniIsRequired(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('DNI required', CountryResolutionException::COUNTRY_NOT_AVAILABLE, 'ES', 6));

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));

        $this->assertFalse($result);
    }

    public function testSaveAddressIsNeverCalledWhenDniIsRequired(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->method('resolveCountryState')
            ->willThrowException(new CountryResolutionException('DNI required', CountryResolutionException::COUNTRY_NOT_AVAILABLE, 'ES', 6));

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('saveAddress');

        $this->action->execute($this->makeShippingData('ORDER-1', 'ES'));
    }

    // -------------------------------------------------------------------------
    // State passthrough to resolver
    // -------------------------------------------------------------------------

    public function testStateIsPassedToResolverAsAdminArea1(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->expects($this->once())
            ->method('resolveCountryState')
            ->with('US', 'NY', 1)
            ->willReturn(new ResolvedCountryState(21, 5, 'US'));
        $this->setUpCustomer(0);

        $this->action->execute($this->makeShippingData('ORDER-1', 'US', 'John', 'Doe', '123 Main St', '', '10001', 'New York', 'NY'));
    }

    public function testNullStateIsPassedToResolverAsNull(): void
    {
        $this->setUpShop(1);
        $this->addressResolver->expects($this->once())
            ->method('resolveCountryState')
            ->with('US', null, 1)
            ->willReturn(new ResolvedCountryState(21, 0, 'US'));
        $this->setUpCustomer(0);

        $shippingData = new ExpressCheckoutShippingData('ORDER-1', 'John', 'Doe', '123 Main St', '', '10001', 'New York', null, 'US', '5551234567');
        $this->action->execute($shippingData);
    }

    // -------------------------------------------------------------------------
    // Customer ID guard
    // -------------------------------------------------------------------------

    public function testReturnsFalseWhenCustomerIdIsZero(): void
    {
        $this->setUpAvailableCountry(0);

        $this->psCheckoutAddressRepository->expects($this->never())
            ->method('getAddressIdByChecksumAndCustomer');

        $result = $this->action->execute($this->makeShippingData('ORDER-1', 'FR'));

        $this->assertFalse($result);
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
        $this->setUpAvailableCountry(1);
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
        $this->setUpAvailableCountry(1);
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

        $this->setUpAvailableCountry(1);
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

        $this->setUpAvailableCountry(1);
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
        $this->setUpShop(1);
        $this->addressResolver->expects($this->exactly(2))
            ->method('resolveCountryState')
            ->with('FR', $this->anything(), $this->anything())
            ->willThrowException(new CountryResolutionException('not found', CountryResolutionException::COUNTRY_NOT_FOUND, 'FR'));

        $this->action->execute($this->makeShippingData('ORDER-A', 'FR'));
        $this->action->execute($this->makeShippingData('ORDER-B', 'FR'));
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
        $this->setUpAvailableCountry(1);

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
        $this->setUpAvailableCountry(1);

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
        $this->setUpAvailableCountry(1);

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
        $this->setUpAvailableCountry(1);

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
        $this->setUpAvailableCountry(1);

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
