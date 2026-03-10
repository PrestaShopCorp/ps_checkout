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

use PsCheckout\Core\FundingSource\ValueObject\FundingSource;

interface FundingSourceEligibilityCheckerInterface
{
    /**
     * Whether this checker is responsible for the given funding source name.
     *
     * @param FundingSource $fundingSource
     *
     * @return bool
     */
    public function supports(FundingSource $fundingSource): bool;

    /**
     * Returns whether the given funding source is eligible in the current checkout context and settings.
     *
     * @param FundingSource $fundingSource
     *
     * @return bool
     */
    public function isEligible(FundingSource $fundingSource): bool;

    /**
     * Minimum cart order total (inclusive) for the given currency ISO code, or null for no limit.
     *
     * @param string $currency ISO 4217 currency code (e.g. 'EUR', 'PLN')
     *
     * @return float|null
     */
    public function getMinAmount(string $currency): ?float;

    /**
     * Maximum cart order total (inclusive) for the given currency ISO code, or null for no limit.
     *
     * @param string $currency ISO 4217 currency code (e.g. 'EUR', 'PLN')
     *
     * @return float|null
     */
    public function getMaxAmount(string $currency): ?float;
}
