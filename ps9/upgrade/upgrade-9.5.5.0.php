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
 * Update main function for module version 9.5.5.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_9_5_5_0(Ps_checkout $module)
{
    $result = true;
    $db = Db::getInstance();

    try {
        if (!$db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_address` (
            `id_address` int(10) unsigned NOT NULL,
            `id_customer` int(10) unsigned NOT NULL,
            `checksum` varchar(32) NOT NULL,
            PRIMARY KEY (`id_customer`, `checksum`),
            KEY `id_address` (`id_address`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8
        ')) {
            PrestaShopLogger::addLog(
                sprintf('%s: pscheckout_address: %s', __FUNCTION__, $db->getMsgError()),
                4, 1, 'Module', $module->id
            );
            $result = false;
        }
    } catch (Throwable $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
        $result = false;
    }

    try {
        if (!$db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_webhook_event` (
            `id` varchar(50) NOT NULL,
            `event_type` varchar(100) NOT NULL,
            `resource_id` varchar(50) NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT \'processing\',
            `error` text DEFAULT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=UTF8
        ')) {
            PrestaShopLogger::addLog(
                sprintf('%s: pscheckout_webhook_event: %s', __FUNCTION__, $db->getMsgError()),
                4, 1, 'Module', $module->id
            );
            $result = false;
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
