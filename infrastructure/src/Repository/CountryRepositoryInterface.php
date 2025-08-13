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

namespace PsCheckout\Infrastructure\Repository;

interface CountryRepositoryInterface
{
    /**
     * Get the module ISO country codes.
     *
     * @param bool $onlyActive
     *
     * @return array
     */
    public function getModuleCountryCodes(bool $onlyActive = true): array;

    /**
     * Delete module country by ISO code.
     *
     * @param string $isoCode
     *
     * @return bool
     */
    public function deleteModuleCountryByIsoCode(string $isoCode): bool;

    /**
     * @param int $idCountry
     *
     * @return string
     */
    public function getCountryIsoCodeById(int $idCountry): string;

    /**
     * @param int $idCountry
     * @param string $state
     *
     * @return int|null
     */
    public function getStateId(int $idCountry, string $state);
}
