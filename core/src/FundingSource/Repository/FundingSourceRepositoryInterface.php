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

namespace PsCheckout\Core\FundingSource\Repository;

interface FundingSourceRepositoryInterface
{
    /**
     * Get one funding source by key value criteria
     *
     * @param array $keyValueCriteria an associative array of key-value pairs where
     *                                the key represents the column name and the value
     *                                represents the value to search for
     *
     * @return array|null
     */
    public function getOneBy(array $keyValueCriteria);

    /**
     * Get all funding sources for a specific shop
     *
     * @param int $shopId
     *
     * @return array
     */
    public function getAllForSpecificShop(int $shopId): array;

    /**
     * Get all active funding sources for a specific shop
     *
     * @param int $shopId
     *
     * @return array
     */
    public function getAllActiveForSpecificShop(int $shopId): array;

    /**
     * @param array $data
     * @param int $shopId
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function upsert(array $data, int $shopId): bool;

    /**
     * Populate funding sources table with default values
     *
     * @param int $shopId
     *
     * @return void
     */
    public function populateWithDefaultValues(int $shopId);
}
