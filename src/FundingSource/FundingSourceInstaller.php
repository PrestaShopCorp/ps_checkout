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

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;

class FundingSourceInstaller
{
    /**
     * Saves Funding Sources for the first time into the database
     *
     * @param int|null $shopId
     *
     * @return bool
     */
    public function createFundingSources($shopId = null)
    {
        $fundingSourceConfigurationRepository = new FundingSourceConfigurationRepository(new PrestaShopContext());
        $fundingSourceCollectionBuilder = new FundingSourceCollectionBuilder(
            new FundingSourceConfiguration($fundingSourceConfigurationRepository),
            new FundingSourceEligibilityConstraint()
        );

        $fundingSourceCollection = $fundingSourceCollectionBuilder->create();
        foreach ($fundingSourceCollection as $fundingSourceEntity) {
            $fundingSourceConfigurationRepository->save([
                'name' => $fundingSourceEntity->getName(),
                'position' => $fundingSourceEntity->getPosition(),
                'isEnabled' => !in_array($fundingSourceEntity->getName(), ['google_pay', 'apple_pay']) ? 1 : 0,
            ], $shopId);
        }

        return true;
    }

    public function createFundingSourcesOnAllShops()
    {
        $result = true;

        foreach (\Shop::getShops(false, null, true) as $shopId) {
            $result &= $this->createFundingSources((int) $shopId);
        }

        return $result;
    }
}
