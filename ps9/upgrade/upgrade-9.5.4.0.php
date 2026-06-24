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
 * Update main function for module version 9.5.4.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_9_5_4_0(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();

        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_order`');

        if (!empty($fields)) {
            $hasDateAdd = false;

            foreach ($fields as $field) {
                if ($field['Field'] === 'date_add') {
                    $hasDateAdd = true;
                }
            }

            if (!$hasDateAdd) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_order`
                    ADD COLUMN `date_add` datetime DEFAULT NULL
                ');
            }
        }
    } catch (\Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
