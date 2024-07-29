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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

class FundingSourceConfiguration
{
    /**
     * @var FundingSourceConfigurationRepository
     */
    private $repository;

    /**
     * @param FundingSourceConfigurationRepository $repository
     */
    public function __construct(FundingSourceConfigurationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the funding source position stored in database configuration if exists or the default position
     *
     * @param string $fundingSourceName
     * @param int $defaultPosition
     *
     * @return int
     */
    public function getPosition($fundingSourceName, $defaultPosition)
    {
        $fundingSource = $this->repository->get($fundingSourceName);

        if ($fundingSource) {
            return (int) $fundingSource['position'];
        }

        return $defaultPosition;
    }

    /**
     * @param string $fundingSourceName
     *
     * @return bool
     */
    public function isEnabled($fundingSourceName)
    {
        $fundingSource = $this->repository->get($fundingSourceName);

        if ($fundingSource) {
            return (bool) $fundingSource['active'];
        }

        return false;
    }
}
