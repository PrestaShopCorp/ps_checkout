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

namespace PsCheckout\Presentation\Presenter\FundingSource;

use PsCheckout\Core\FundingSource\ValueObject\FundingSource;

interface FundingSourcePresenterInterface
{
    /**
     * Get one funding source by key value criteria
     *
     * @param array $keyValueCriteria an associative array of key-value pairs where
     *                                the key represents the column name and the value
     *                                represents the value to search for
     *
     * @return FundingSource|null
     */
    public function getOneBy(array $keyValueCriteria);

    /**
     * Get all funding sources for specific shop
     *
     * @param int $shopId
     *
     * @return FundingSource[]
     */
    public function getAllForSpecificShop(int $shopId): array;

    /**
     * Get all active funding sources for specific shop
     *
     * @param int $shopId
     *
     * @return FundingSource[]
     */
    public function getAllActiveForSpecificShop(int $shopId): array;
}
