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
    // Create session tables
    $result = Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'pscheckout_onboarding_session (
            correlation_id VARCHAR(255) NOT NULL,
            user_id INT NOT NULL,
            shop_id INT NOT NULL,
            is_closed INT NOT NULL,
            auth_token VARCHAR(255),
            status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            closed_at DATETIME,
            expires_at DATETIME,
            is_sse_opened TINYINT(1) DEFAULT 0,
            data TEXT,
            PRIMARY KEY (user_id, shop_id, is_closed)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    $result &= Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'pscheckout_payment_session (
            correlation_id VARCHAR(255) NOT NULL,
            user_id INT NOT NULL,
            shop_id INT NOT NULL,
            is_closed INT NOT NULL,
            auth_token VARCHAR(255),
            status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            closed_at DATETIME,
            expires_at DATETIME,
            is_sse_opened TINYINT(1) DEFAULT 0,
            data TEXT,
            PRIMARY KEY (user_id, shop_id, is_closed)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    return $result;
}
