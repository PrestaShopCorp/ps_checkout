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
 * Update main function for module version 8.4.0.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_8_4_0_0($module)
{
    try {
        $db = Db::getInstance();
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_order` (
            `id` varchar(50) NOT NULL,
            `id_cart` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `funding_source` varchar(50) NOT NULL,
            `payment_source` text,
            `environment` varchar(50) NOT NULL,
            `is_card_fields` tinyint(1) NOT NULL,
            `is_express_checkout` tinyint(1) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_capture` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `final_capture` tinyint(1) NOT NULL,
            `created_at` varchar(50) NOT NULL,
            `updated_at` varchar(50) NOT NULL,
            `seller_protection` varchar(50) NOT NULL,
            `seller_receivable_breakdown` text,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_refund` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `invoice_id` varchar(50) NOT NULL,
            `custom_id` varchar(50) NOT NULL,
            `acquirer_reference_number` varchar(50) NOT NULL,
            `seller_payable_breakdown` text,
            `id_order_slip` INT(10) UNSIGNED,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_authorization` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `expiration_time` varchar(50) NOT NULL,
            `seller_protection` varchar(50) NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_purchase_unit` (
            `id_order` varchar(50) NOT NULL,
            `checksum` varchar(50) NOT NULL,
            `reference_id` varchar(50) NOT NULL,
            `items` text,
            PRIMARY KEY (`reference_id`, `id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
