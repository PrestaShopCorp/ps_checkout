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

namespace Tests\Unit\PsCheckout\Infrastructure\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Service\CountryResolutionException;
use PsCheckout\Infrastructure\Service\PaypalAddressResolver;
use PsCheckout\Infrastructure\Service\ResolvedCountryState;

class PaypalAddressResolverTest extends TestCase
{
    /** @var CountryInterface|MockObject */
    private $country;

    /** @var CountryRepositoryInterface|MockObject */
    private $countryRepository;

    /** @var PaypalAddressResolver */
    private $resolver;

    protected function setUp(): void
    {
        $this->country = $this->createMock(CountryInterface::class);
        $this->countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $this->resolver = new PaypalAddressResolver($this->country, $this->countryRepository);
    }

    public function testReturnsResolvedCountryStateForKnownAvailableCountryWithoutStates(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(8);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);
        $this->country->method('containsStates')->willReturn(false);

        $result = $this->resolver->resolveCountryState('FR', null, 1);

        $this->assertInstanceOf(ResolvedCountryState::class, $result);
        $this->assertSame(8, $result->idCountry);
        $this->assertSame(0, $result->idState);
        $this->assertSame('FR', $result->shopIsoCode);
    }

    public function testResolvesStateByIsoCodeForCountriesRequiringIso(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(21);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);
        $this->country->method('containsStates')->willReturn(true);
        $this->countryRepository->method('getStateIdByIsoCode')->with(21, 'CA')->willReturn(5);

        $result = $this->resolver->resolveCountryState('US', 'CA', 1);

        $this->assertSame(21, $result->idCountry);
        $this->assertSame(5, $result->idState);
        $this->assertSame('US', $result->shopIsoCode);
    }

    public function testResolvesStateByNameForCountriesNotRequiringIso(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(13);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);
        $this->country->method('containsStates')->willReturn(true);
        $this->countryRepository->method('getStateId')->with(13, 'Bavaria')->willReturn(7);

        $result = $this->resolver->resolveCountryState('DE', 'Bavaria', 1);

        $this->assertSame(13, $result->idCountry);
        $this->assertSame(7, $result->idState);
    }

    public function testThrowsCountryNotFoundWhenIdIsZero(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(0);

        try {
            $this->resolver->resolveCountryState('ZZ', null, 1);
            $this->fail('Expected CountryResolutionException');
        } catch (CountryResolutionException $e) {
            $this->assertSame(CountryResolutionException::COUNTRY_NOT_FOUND, $e->getCode());
            $this->assertSame('ZZ', $e->getShopIsoCode());
            $this->assertSame(0, $e->getIdCountry());
        }
    }

    public function testThrowsCountryNotAvailableWhenNotAvailableForDelivery(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(5);
        $this->country->method('isAvailableForDelivery')->willReturn(false);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);

        try {
            $this->resolver->resolveCountryState('CN', null, 1);
            $this->fail('Expected CountryResolutionException');
        } catch (CountryResolutionException $e) {
            $this->assertSame(CountryResolutionException::COUNTRY_NOT_AVAILABLE, $e->getCode());
            $this->assertSame(5, $e->getIdCountry());
        }
    }

    public function testThrowsCountryNotAvailableWhenDniRequired(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(6);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(true);

        try {
            $this->resolver->resolveCountryState('ES', null, 1);
            $this->fail('Expected CountryResolutionException');
        } catch (CountryResolutionException $e) {
            $this->assertSame(CountryResolutionException::COUNTRY_NOT_AVAILABLE, $e->getCode());
            $this->assertSame(6, $e->getIdCountry());
        }
    }

    public function testIdStateIsZeroWhenAdminArea1IsNull(): void
    {
        $this->country->method('getIdByIsoCode')->willReturn(21);
        $this->country->method('isAvailableForDelivery')->willReturn(true);
        $this->country->method('isNeedDniByCountryId')->willReturn(false);
        $this->country->method('containsStates')->willReturn(true);
        $this->countryRepository->expects($this->never())->method('getStateIdByIsoCode');
        $this->countryRepository->expects($this->never())->method('getStateId');

        $result = $this->resolver->resolveCountryState('US', null, 1);

        $this->assertSame(0, $result->idState);
    }
}
