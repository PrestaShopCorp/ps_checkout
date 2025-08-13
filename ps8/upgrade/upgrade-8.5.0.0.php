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
 * Update main function for module version 8.5.0.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_8_5_0_0(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_tracking` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_order` int(10) unsigned NOT NULL,
            `tracking_number` varchar(64) NOT NULL,
            `carrier_id` int(10) unsigned NOT NULL,
            `carrier_name` varchar(64) NOT NULL,
            `paypal_order_id` varchar(50) NOT NULL,
            `paypal_capture_id` varchar(50) NOT NULL,
            `tracker_id` varchar(64) DEFAULT NULL,
            `items` text DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT "PENDING",
            `paypal_tracking_status` varchar(20) DEFAULT NULL,
            `payload_checksum` varchar(64) NOT NULL,
            `sent_to_paypal` tinyint(1) NOT NULL DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `id_order` (`id_order`),
            KEY `tracking_number` (`tracking_number`),
            KEY `paypal_order_id` (`paypal_order_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');

        $module->registerHook('actionGetOrderShipments');
        $module->registerHook('actionObjectOrderCarrierUpdateAfter');

        // Remove Giropay
        Db::getInstance()->delete(
            'pscheckout_funding_source',
            'name LIKE "giropay"'
        );
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
