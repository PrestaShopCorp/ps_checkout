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

use Currency as PrestaShopCurrency;

class CurrencyRepository implements CurrencyRepositoryInterface
{
    /**
     * @var string
     */
    private $moduleName;

    public function __construct(string $moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function getModuleCurrencyCodes(bool $onlyActive = true): array
    {
        $active = $onlyActive ? ' AND c.active = 1' : null;

        $shopCodes = \Db::getInstance()->executeS(
            '
            SELECT c.iso_code
            FROM ' . _DB_PREFIX_ . 'currency c
            JOIN ' . _DB_PREFIX_ . 'module_currency mc ON mc.id_currency = c.id_currency
            JOIN ' . _DB_PREFIX_ . 'module m ON m.id_module = mc.id_module
            WHERE m.name = "' . $this->moduleName . '"
            AND mc.id_shop = ' . (int) \Context::getContext()->shop->id
            . $active
        );

        return $shopCodes ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteModuleCurrencyByIsoCode(string $isoCode): bool
    {
        return \Db::getInstance()->execute(
            '
                DELETE FROM ' . _DB_PREFIX_ . 'module_currency
                WHERE id_currency = (SELECT id_currency FROM ' . _DB_PREFIX_ . 'currency WHERE iso_code = "' . pSQL($isoCode) . '")
                AND id_module = ' . (int) \Module::getModuleIdByName($this->moduleName) . '
                AND id_shop = ' . (int) \Context::getContext()->shop->id
        ) ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdByIsoCode(string $isoCode, int $shopId = 0): int
    {
        return (int) PrestaShopCurrency::getIdByIsoCode($isoCode, $shopId);
    }
}
