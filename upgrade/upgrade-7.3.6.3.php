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
 * Update main function for module version 7.3.6.3
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_3_6_3($module)
{
    try {
        $db = Db::getInstance();
        $db->update('pscheckout_cart', ['paypal_token' => null, 'paypal_token_expire' => null], 'paypal_token IS NOT NULL', 0, true);
        $db->update('pscheckout_cart', ['paypal_status' => 'CANCELED'], 'paypal_status = "CREATED" AND date_add < DATE_SUB(NOW(), INTERVAL 1 HOUR)');
        $db->delete('pscheckout_cart', 'paypal_order IS NULL OR paypal_order = ""');
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);
    }

    return true;
}
