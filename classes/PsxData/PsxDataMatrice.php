<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\PsxDataMatrice;

class PsxDataMatrice
{
    /**
     * Transforme Company EMR data (from PSX) to a Average Monthly Volume Range (for Payment)
     * "lt5000": "Up to USD $5000 USD",
     * "lt25000": "USD $5000 - USD $24 999",
     * "lt50000": "USD $25 000 - USD $49 99",
     * "lt100000": "USD $50 000 USD - USD $99 999 USD",
     * "lt250000": "USD $100 000 - USD $249 999",
     * "lt500000": "USD $250 000 - USD $499 999",
     * "lt1000000": "USD $500 000 - USD $999 999",
     * "gt1000000": "More than USD $1 000 000"
     *
     * @param string $type
     *
     * @return array
     */
    public function getCompanyEmrToAverageMonthlyVolumeRange($type)
    {
        $currency = 'EUR';
        $minimumAmount = 0;
        $maximumAmount = 0;

        if ('lt5000' === $type) {
            $minimumAmount = 0;
            $maximumAmount = 5000;
        }

        if ('lt25000' === $type) {
            $minimumAmount = 5000;
            $maximumAmount = 24999;
        }

        if ('lt50000' === $type) {
            $minimumAmount = 25000;
            $maximumAmount = 49999;
        }

        if ('lt100000' === $type) {
            $minimumAmount = 50000;
            $maximumAmount = 99999;
        }

        if ('lt250000' === $type) {
            $minimumAmount = 100000;
            $maximumAmount = 249999;
        }

        if ('lt500000' === $type) {
            $minimumAmount = 250000;
            $maximumAmount = 499999;
        }

        if ('lt1000000' === $type) {
            $minimumAmount = 500000;
            $maximumAmount = 999999;
        }

        if ('gt1000000' === $type) {
            $minimumAmount = 1000000;
            $maximumAmount = 9999999999;
        }

        return [
            'maximum_amount' => [
                'currency' => $currency,
                'value' => $maximumAmount,
            ],
            'minimum_amount' => [
                'currency' => $currency,
                'value' => $minimumAmount,
            ],
        ];
    }
}
