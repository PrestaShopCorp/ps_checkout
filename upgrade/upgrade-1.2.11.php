<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\PrestashopCheckout\Database\TableManager;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module Version 1.2.11
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_2_11($module)
{
    return \Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . TableManager::TABLE_ORDER_MATRICE . '` CHANGE `id_order_prestashop` `id_order_prestashop` INT(10) UNSIGNED NOT NULL;');
}
