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

use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;

class IdealEligibilityChecker extends BaseFundingSourceEligibilityChecker
{
    protected function getSupportedName(): string
    {
        return 'ideal';
    }

    protected function getAllowedCurrenciesIsoCodes(): array
    {
        return ['EUR'];
    }

    protected function getSupportedIntents(): array
    {
        return [PayPalOrderIntent::CAPTURE];
    }

    protected function assertConfigurations(): array
    {
        return [];
    }

    public function getMinAmount(string $currency): ?float
    {
        return ['EUR' => 0.01][$currency] ?? null;
    }

    protected function getSupportedMerchantCountries(): array
    {
        return [];
    }
}
