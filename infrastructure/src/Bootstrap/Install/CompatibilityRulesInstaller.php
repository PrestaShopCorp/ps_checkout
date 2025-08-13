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

namespace PsCheckout\Infrastructure\Bootstrap\Install;

use PsCheckout\Core\Settings\Configuration\PayPalCodeConfiguration;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\CurrencyRepositoryInterface;
use PsCheckout\Utility\Common\ArrayUtility;

class CompatibilityRulesInstaller implements InstallerInterface
{
    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var CurrencyRepositoryInterface
     */
    private $currencyRepository;

    public function __construct(
        CountryRepositoryInterface $countryRepository,
        CurrencyRepositoryInterface $currencyRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->currencyRepository = $currencyRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function init(): bool
    {
        return $this->disableIncompatibleCountries() && $this->disableIncompatibleCurrencies();
    }

    /**
     * Disable incompatible countries with PayPal for PrestaShop Checkout
     *
     * @return bool
     */
    private function disableIncompatibleCountries()
    {
        $moduleCountryCodes = $this->countryRepository->getModuleCountryCodes(false);

        // Extract the 'iso_code' values
        $moduleCountryIsoCodes = array_map(function ($item) {
            return $item['iso_code'];
        }, $moduleCountryCodes);

        $incompatibleCodes = ArrayUtility::findMissingKeys($moduleCountryIsoCodes, PayPalCodeConfiguration::getCountryCodes());

        $result = true;

        foreach ($incompatibleCodes as $incompatibleCode) {
            // Delete incompatible country from module_country table
            $result &= $this->countryRepository->deleteModuleCountryByIsoCode($incompatibleCode);
        }

        return $result;
    }

    /**
     * Disable incompatible currencies with PayPal for PrestaShop Checkout
     *
     * @return bool
     */
    private function disableIncompatibleCurrencies()
    {
        $moduleCurrencyCodes = $this->currencyRepository->getModuleCurrencyCodes(false);

        // Extract the 'iso_code' values
        $moduleCurrenciesIsoCodes = array_map(function ($item) {
            return $item['iso_code'];
        }, $moduleCurrencyCodes);

        $incompatibleCodes = ArrayUtility::findMissingKeys($moduleCurrenciesIsoCodes, PayPalCodeConfiguration::getCurrencyCodes());

        $result = true;

        foreach ($incompatibleCodes as $incompatibleCode) {
            // Delete incompatible currency from module_currency table
            $result &= $this->currencyRepository->deleteModuleCurrencyByIsoCode($incompatibleCode);
        }

        return $result;
    }
}
