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

namespace PsCheckout\Core\FundingSource\Eligibility\Checker;

use PsCheckout\Core\FundingSource\Constraint\FundingSourceConstraint;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;

class PaylaterEligibilityChecker extends BaseFundingSourceEligibilityChecker
{
    /**
     * Pay Later amount range per buyer country, in the country's currency (values as of 2026-09):
     * the union of the country's Pay Later offers, PayPal picks the offer matching the amount.
     *
     * @see https://developer.paypal.com/pay-later/overview
     *
     * @var array<string, array{min: float, max: float}>
     */
    private const AMOUNT_RANGES = [
        'AU' => ['min' => 1.0, 'max' => 1999.99],
        'CA' => ['min' => 10.0, 'max' => 2000.0],
        'DE' => ['min' => 1.0, 'max' => 10000.0],
        'ES' => ['min' => 20.0, 'max' => 5000.0],
        'FR' => ['min' => 20.0, 'max' => 3000.0],
        'GB' => ['min' => 1.0, 'max' => 3000.0],
        'IT' => ['min' => 20.0, 'max' => 5000.0],
        'US' => ['min' => 10.0, 'max' => 10000.0],
    ];

    /**
     * @inheritDoc
     */
    public function isEligible(FundingSource $fundingSource): bool
    {
        if ($this->getAmountRange($this->context->getCurrencyIsoCode()) === null) {
            return false;
        }

        return parent::isEligible($fundingSource);
    }

    protected function getSupportedName(): string
    {
        return 'paylater';
    }

    protected function getAllowedCurrenciesIsoCodes(): array
    {
        return array_values(array_unique(FundingSourceConstraint::getPayLaterCurrencies()));
    }

    protected function getSupportedIntents(): array
    {
        return [PayPalOrderIntent::CAPTURE];
    }

    protected function assertConfigurations(): array
    {
        return [];
    }

    protected function getSupportedMerchantCountries(): array
    {
        return [];
    }

    /**
     * Depends on the buyer country. Null means Pay Later is not offered (see isEligible), not "no limit".
     */
    public function getMinAmount(string $currency): ?float
    {
        $range = $this->getAmountRange($currency);

        return $range !== null ? $range['min'] : null;
    }

    /**
     * Depends on the buyer country. Null means Pay Later is not offered (see isEligible), not "no limit".
     */
    public function getMaxAmount(string $currency): ?float
    {
        $range = $this->getAmountRange($currency);

        return $range !== null ? $range['max'] : null;
    }

    /**
     * Range for the buyer country, or null when the country has none or $currency is not its currency.
     *
     * @return array{min: float, max: float}|null
     */
    private function getAmountRange(string $currency): ?array
    {
        $country = $this->countryResolver->getBuyerCountryIsoCode();

        if ((FundingSourceConstraint::getPayLaterCurrencies()[$country] ?? null) !== $currency) {
            return null;
        }

        return self::AMOUNT_RANGES[$country] ?? null;
    }
}
