<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Tests\Unit\FundingSource;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceCollection;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceEntity;

class FundingSourceCollectionTest extends TestCase
{
    public function testSortFundingSourcesByPosition()
    {
        $collection = new FundingSourceCollection($this->getFundingSourceEntityList());
        $sortedFundingSources = $collection->sortByPosition()->get();

        for ($i = 1; $i < count($sortedFundingSources); ++$i) {
            $this->assertGreaterThan(
                $sortedFundingSources[$i - 1]->getPosition(),
                $sortedFundingSources[$i]->getPosition()
            );
        }
    }

    public function testEligibleFundingSources()
    {
        $collection = new FundingSourceCollection($this->getFundingSourceEntityList());

        foreach ($collection->filterEligibility()->get() as $fundingSource) {
            $this->assertTrue($fundingSource->getIsEnabled());
        }
    }

    /**
     * @return FundingSourceEntity[]
     */
    public function getFundingSourceEntityList()
    {
        // PayPal
        $paypal = new FundingSourceEntity('paypal');
        $paypal->setPosition(1);
        $paypal->setIsToggleable(false);

        // Credit card
        $card = new FundingSourceEntity('card');
        $card->setPosition(2);
        $card->setIsEnabled(true);

        // Bancontact
        $bancontact = new FundingSourceEntity('bancontact');
        $bancontact->setPosition(3);
        $bancontact->setIsEnabled(false);
        $bancontact->setCountries(['BE']);

        return [$paypal, $card, $bancontact];
    }
}
