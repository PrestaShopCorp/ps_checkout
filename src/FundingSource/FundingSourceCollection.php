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

class FundingSourceCollection
{
    /**
     * @var FundingSourceEntity[]
     */
    private $fundingSources;

    /**
     * @param FundingSourceEntity[] $fundingSources
     */
    public function __construct($fundingSources)
    {
        $this->fundingSources = $fundingSources;
    }

    /**
     * Get the funding sources
     *
     * @return FundingSourceEntity[]
     */
    public function get()
    {
        return $this->fundingSources;
    }

    /**
     * Sort funding sources by position
     *
     * @return FundingSourceCollection
     */
    public function sortByPosition()
    {
        usort($this->fundingSources, function ($a, $b) {
            return $a->getPosition() - $b->getPosition();
        });

        return $this;
    }

    /**
     * Filter funding sources by eligibility
     *
     * @return FundingSourceCollection
     */
    public function filterEligibility()
    {
        $eligibleFundingSources = [];

        foreach ($this->fundingSources as $fundingSource) {
            if (true === $fundingSource->getIsEnabled()) {
                $eligibleFundingSources[] = $fundingSource;
            }
        }

        $this->fundingSources = $eligibleFundingSources;

        return $this;
    }
}
