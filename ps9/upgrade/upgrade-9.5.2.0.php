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
 * Update main function for module version 9.5.2.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_9_5_2_0(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();

        // Check columns in pscheckout_authorization table
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_authorization`');

        if (!empty($fields)) {
            $hasCreateTime = false;
            $hasUpdateTime = false;
            $hasSellerProtection = false;

            foreach ($fields as $field) {
                if ($field['Field'] === 'create_time') {
                    $hasCreateTime = true;
                }
                if ($field['Field'] === 'update_time') {
                    $hasUpdateTime = true;
                }
                if ($field['Field'] === 'seller_protection') {
                    $hasSellerProtection = true;
                }
            }

            // Add create_time column if it doesn't exist
            if (!$hasCreateTime) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    ADD COLUMN `create_time` varchar(20) NOT NULL DEFAULT ""
                ');
            }

            // Add update_time column if it doesn't exist
            if (!$hasUpdateTime) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    ADD COLUMN `update_time` varchar(20) NOT NULL DEFAULT ""
                ');
            }

            // Drop seller_protection column if it exists
            if ($hasSellerProtection) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    DROP COLUMN `seller_protection`
                ');
            }
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    $module->registerHook('actionOrderStatusPostUpdate');

    return true;
}
