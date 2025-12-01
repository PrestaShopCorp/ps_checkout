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
 * Update main function for module version 9.5.0.7
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_9_5_0_7(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();

        // Add Venmo funding source for all shops
        $shops = Shop::getShops(false, null, true);

        foreach ($shops as $shopId) {
            // Get the highest position for this shop
            $maxPosition = (int) $db->getValue('
                SELECT MAX(position)
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `id_shop` = ' . (int) $shopId
            );

            // Check if Venmo already exists for this shop
            $venmoExists = $db->getValue('
                SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `name` = "venmo" AND `id_shop` = ' . (int) $shopId
            );

            // Insert Venmo if it doesn't exist (disabled by default, positioned after card)
            if (!$venmoExists) {
                $db->insert('pscheckout_funding_source', [
                    'name' => 'venmo',
                    'active' => 0,
                    'position' => (int) ($maxPosition + 1),
                    'id_shop' => (int) $shopId,
                ]);
            }
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
