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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

class CountryRepository
{
    /**
     * Get country by ISO
     *
     * @param string $country
     *
     * @return int
     */
    public function getByIso($country)
    {
        return (int) \Country::getByIso($country);
    }

    /**
     * Get country names
     *
     * @return array
     */
    public function getCountryNames($countries)
    {
        $names = [];

        foreach ($countries as $country) {
            $names[] = \Country::getNameById((int) \Context::getContext()->language->id, $this->getByIso($country));
        }

        return $names;
    }

    /**
     * @param string $state
     *
     * @return int
     */
    public function getStateId($state)
    {
        $query = new \DbQuery();
        $query->select('id_state');
        $query->from('state');
        $query->where('name LIKE \'%' . pSQL($state) . '%\' OR iso_code LIKE \'%' . pSQL($state) . '%\'');
        $query->where('active = 1');

        return (int) \Db::getInstance()->getValue($query);
    }
}
