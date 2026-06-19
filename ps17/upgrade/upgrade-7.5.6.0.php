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
 * Update main function for module version 7.5.6.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_5_6_0($module)
{
    $result = true;
    $db = Db::getInstance();

    try {
        if (!$db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_carrier` (
            `id_reference` int(10) unsigned NOT NULL,
            `type` varchar(20) NOT NULL DEFAULT "SHIPPING",
            `disabled` tinyint(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_reference`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8
        ')) {
            PrestaShopLogger::addLog(
                sprintf('%s: pscheckout_carrier: %s', __FUNCTION__, $db->getMsgError()),
                4, 1, 'Module', $module->id
            );
            $result = false;
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_cart`');
        if (!empty($fields)) {
            $hasEnvironment = false;
            foreach ($fields as $field) {
                if ($field['Field'] === 'paypal_token' && $field['Type'] !== 'text') {
                    $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` CHANGE `paypal_token` `paypal_token` text DEFAULT NULL');
                }
                if ($field['Field'] === 'paypal_status' && $field['Type'] !== 'varchar(30)') {
                    $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` CHANGE `paypal_status` `paypal_status` varchar(30) NULL');
                }
                if ($field['Field'] === 'environment') {
                    $hasEnvironment = true;
                }
            }
            if (!$hasEnvironment) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` ADD COLUMN `environment` varchar(20) DEFAULT NULL');
            }
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_order`');
        if (!empty($fields)) {
            $hasTags = false;
            $hasDateAdd = false;
            foreach ($fields as $field) {
                if ($field['Field'] === 'tags') {
                    $hasTags = true;
                }
                if ($field['Field'] === 'date_add') {
                    $hasDateAdd = true;
                }
            }
            if (!$hasTags) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_order` ADD COLUMN `tags` varchar(255) DEFAULT NULL');
            }
            if (!$hasDateAdd) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_order` ADD COLUMN `date_add` datetime DEFAULT NULL');
            }
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_carrier`');
        if (!empty($fields)) {
            $hasDisabled = false;
            foreach ($fields as $field) {
                if ($field['Field'] === 'disabled') {
                    $hasDisabled = true;
                }
            }
            if (!$hasDisabled) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_carrier` ADD COLUMN `disabled` tinyint(1) NOT NULL DEFAULT 0');
            }
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_authorization`');
        if (!empty($fields)) {
            $hasCreateTime = false;
            $hasUpdateTime = false;
            foreach ($fields as $field) {
                if ($field['Field'] === 'seller_protection') {
                    $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization` DROP COLUMN `seller_protection`');
                }
                if ($field['Field'] === 'create_time') {
                    $hasCreateTime = true;
                }
                if ($field['Field'] === 'update_time') {
                    $hasUpdateTime = true;
                }
            }
            if (!$hasCreateTime) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization` ADD COLUMN `create_time` varchar(20) NOT NULL DEFAULT ""');
            }
            if (!$hasUpdateTime) {
                $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization` ADD COLUMN `update_time` varchar(20) NOT NULL DEFAULT ""');
            }
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        if (!\Hook::getIdByName('actionGetPsCheckoutCarrierType')) {
            $hook = new \Hook();
            $hook->name = 'actionGetPsCheckoutCarrierType';
            $hook->title = 'Get ps_checkout carrier type';
            $hook->description = 'Allows external modules to override a carrier type (SHIPPING or PICKUP) and disabled state for the PayPal shipping overlay. Receives id_carrier, id_reference (0 when carrier has no pscheckout_carrier row), type (by ref), disabled (by ref).';
            $hook->position = 1;
            if (!$hook->add()) {
                PrestaShopLogger::addLog(
                    sprintf('%s: failed to register hook actionGetPsCheckoutCarrierType', __FUNCTION__),
                    3, 1, 'Module', $module->id
                );
                $result = false;
            }
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    if ($result) {
        PrestaShopLogger::addLog(sprintf('%s: successful', __FUNCTION__), 1, 1, 'Module', $module->id);
    }

    return $result;
}
