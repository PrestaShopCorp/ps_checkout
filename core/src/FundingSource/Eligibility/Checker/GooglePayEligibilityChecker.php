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
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;

class GooglePayEligibilityChecker extends BaseFundingSourceEligibilityChecker
{
    protected function getSupportedName(): string
    {
        return 'google_pay';
    }

    protected function getAllowedCurrenciesIsoCodes(): array
    {
        return ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'];
    }

    protected function getSupportedIntents(): array
    {
        return [PayPalOrderIntent::CAPTURE, PayPalOrderIntent::AUTHORIZE];
    }

    protected function assertConfigurations(): array
    {
        return [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY];
    }

    protected function getSupportedMerchantCountries(): array
    {
        return [];
    }
}
