<?php

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;

class FundingSourceInstaller
{
    /**
     * Saves Funding Sources for the first time into the database
     *
     * @return bool
     */
    public function createFundingSources()
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
                'isEnabled' => $fundingSourceEntity->getIsEnabled() ? 1 : 0,
            ]);
        }

        return true;
    }
}
