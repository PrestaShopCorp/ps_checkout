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

interface PaypalAddressResolverInterface
{
    /**
     * Resolve a PayPal country code and optional state string to PrestaShop IDs.
     *
     * @param string $paypalCountryCode PayPal two-letter country code
     * @param string|null $adminArea1 PayPal admin_area_1 (state/province name or ISO code)
     * @param int $idShop Current shop ID (used for delivery availability check)
     *
     * @throws CountryResolutionException when the country is unknown or not available for delivery
     *
     * @return ResolvedCountryState
     */
    public function resolveCountryState(string $paypalCountryCode, ?string $adminArea1, int $idShop): ResolvedCountryState;
}
