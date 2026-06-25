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

namespace PsCheckout\Infrastructure\Service;

use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;
use PsCheckout\Utility\Payload\PaypalCountryCodeUtility;
use PsCheckout\Utility\Payload\PaypalStateCodeMapUtility;

class PaypalAddressResolver implements PaypalAddressResolverInterface
{
    /**
     * @var CountryInterface
     */
    private $country;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    public function __construct(CountryInterface $country, CountryRepositoryInterface $countryRepository)
    {
        $this->country = $country;
        $this->countryRepository = $countryRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveCountryState(string $paypalCountryCode, ?string $adminArea1, int $idShop): ResolvedCountryState
    {
        $shopIsoCode = PaypalCountryCodeUtility::getShopIsoCode($paypalCountryCode);
        $idCountry = (int) $this->country->getIdByIsoCode($shopIsoCode);

        if (!$idCountry) {
            throw new CountryResolutionException(
                sprintf('Country not found for code: %s', $paypalCountryCode),
                CountryResolutionException::COUNTRY_NOT_FOUND,
                $shopIsoCode
            );
        }

        if (!$this->country->isAvailableForDelivery($idCountry, $idShop)
            || $this->country->isNeedDniByCountryId($idCountry)
        ) {
            throw new CountryResolutionException(
                sprintf('Country %s (id=%d) is not available for delivery', $shopIsoCode, $idCountry),
                CountryResolutionException::COUNTRY_NOT_AVAILABLE,
                $shopIsoCode,
                $idCountry
            );
        }

        $idState = 0;

        if ($this->country->containsStates($idCountry) && $adminArea1) {
            if (PaypalAddressRequirementsUtility::usesStateIsoCode($shopIsoCode)) {
                $psIsoCode = PaypalStateCodeMapUtility::getShopStateCode($shopIsoCode, $adminArea1);
                $idState = $this->countryRepository->getStateIdByIsoCode($idCountry, $psIsoCode);
            } else {
                $idState = (int) $this->countryRepository->getStateId($idCountry, $adminArea1);
            }
        }

        return new ResolvedCountryState($idCountry, $idState, $shopIsoCode);
    }
}
