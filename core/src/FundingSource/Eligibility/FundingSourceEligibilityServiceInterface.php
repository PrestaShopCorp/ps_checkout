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

namespace PsCheckout\Core\FundingSource\Eligibility;

use PsCheckout\Core\FundingSource\ValueObject\FundingSource;

interface FundingSourceEligibilityServiceInterface
{
    /**
     * Returns eligible Alternative Payment Methods (APM) names according to current
     * context (country/currency), settings, intent and boolean configuration.
     *
     * @return array<string, FundingSource> list of eligible APM funding sources (e.g. bancontact, blik, eps, ideal, mybank, p24)
     */
    public function getEligibleFundingSources(): array;

    /**
     * Returns whether a funding source is eligible to be exposed as a payment option in the current context.
     * Non-APM funding sources (paypal, card, paylater) are considered eligible if enabled by configuration.
     *
     * @param FundingSource $fundingSource
     *
     * @return bool
     */
    public function isFundingSourceEligible(FundingSource $fundingSource): bool;
}
