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

use DbQuery;

class CountryRepository implements CountryRepositoryInterface
{
    /**
     * @var string
     */
    private $moduleName;

    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleCountryCodes(bool $onlyActive = true): array
    {
        $active = $onlyActive ? ' AND c.active = 1' : null;

        $shopCodes = \Db::getInstance()->executeS(
            '
            SELECT c.iso_code
            FROM ' . _DB_PREFIX_ . 'country c
            JOIN ' . _DB_PREFIX_ . 'module_country mc ON mc.id_country = c.id_country
            JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = mc.id_module
            WHERE m.name = "' . pSQL($this->moduleName) . '"
            AND mc.id_shop = ' . (int) \Context::getContext()->shop->id
            . $active
        );

        return $shopCodes ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteModuleCountryByIsoCode(string $isoCode): bool
    {
        return \Db::getInstance()->execute(
            '
                DELETE FROM ' . _DB_PREFIX_ . 'module_country
                WHERE id_country = (SELECT id_country FROM ' . _DB_PREFIX_ . 'country WHERE iso_code = "' . pSQL($isoCode) . '")
                AND id_module = ' . (int) \Module::getModuleIdByName($this->moduleName) . '
                AND id_shop = ' . (int) \Context::getContext()->shop->id
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryIsoCodeById(int $idCountry): string
    {
        return \Country::getIsoById($idCountry) ?: '';
    }

    /**
     * {@inheritDoc}
     */
    public function getStateId(int $idCountry, string $state)
    {
        $query = new DbQuery();
        $query->select('id_state');
        $query->from('state');
        $query->where('name LIKE \'%' . pSQL($state) . '%\'');
        $query->where('active = 1');
        $query->where('id_country = ' . (int) $idCountry);

        return (int) \Db::getInstance()->getValue($query) ?? null;
    }
}
