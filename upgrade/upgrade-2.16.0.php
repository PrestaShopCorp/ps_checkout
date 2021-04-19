<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module version 2.16.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_2_16_0($module)
{
    // Create session table
    return Db::getInstance()->execute("
        CREATE TABLE IF NOT EXISTS " . _DB_PREFIX_ . "pscheckout_session (
            user_id INT NOT NULL,
            shop_id INT NOT NULL,
            process_type VARCHAR(255) NOT NULL,
            account_id VARCHAR(255),
            correlation_id VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            data TEXT,
            creation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expiration_date DATETIME,
            CONSTRAINT pk_pscheckout_session PRIMARY KEY (user_id, shop_id, process_type)
        ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=UTF8;
    ");
}
