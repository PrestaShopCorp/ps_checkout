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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module version 2.2.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_2_2_0($module)
{
    $db = Db::getInstance();

    $createFundingSourceTable = (bool) $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_funding_source` (
              `name` varchar(20) NOT NULL,
              `active` tinyint(1) unsigned DEFAULT 0 NOT NULL,
              `position` tinyint(2) unsigned NOT NULL,
              `id_shop` int unsigned NOT NULL,
              PRIMARY KEY (`name`, `id_shop`),
              INDEX (`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');

    if ($createFundingSourceTable) {
        $shopsList = \Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            $isCardEnabled = (bool) \Configuration::get(
                'PS_CHECKOUT_CARD_PAYMENT_ENABLED',
                null,
                null,
                $shopId
            );
            $hasFundingSourceCard = (bool) $db->getValue('
                SELECT 1
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `name` = "card"
                AND `id_shop` = ' . (int) $shopId
            );

            if (false === $isCardEnabled && false === $hasFundingSourceCard) {
                $db->insert(
                    'pscheckout_funding_source',
                    [
                        'name' => 'card',
                        'position' => 2,
                        'active' => 0,
                        'id_shop' => (int) $shopId,
                    ]
                );
            }
        }
    }

    return true;
}
