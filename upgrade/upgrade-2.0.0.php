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
 * Update main function for module version 2.0.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_2_0_0($module)
{
    $db = Db::getInstance();

    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_MAX_FILES', '30');
    Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_LEVEL', '100');
    Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_HTTP', '1');
    Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_HTTP_FORMAT', 'DEBUG');
    Configuration::updateGlobalValue('PS_CHECKOUT_INTEGRATION_DATE', '2020-07-30');
    $module->registerHook(Ps_checkout::HOOK_LIST);
    $db->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_cart` (
          `id_pscheckout_cart` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `id_cart` int unsigned NOT NULL,
          `paypal_intent` varchar(20) DEFAULT "CAPTURE",
          `paypal_order` varchar(20) NULL,
          `paypal_status` varchar(20) NULL,
          `paypal_funding` varchar(20) NULL,
          `paypal_token` varchar(1024) NULL,
          `paypal_token_expire` datetime NULL,
          `paypal_authorization_expire` datetime NULL,
          `isExpressCheckout` tinyint(1) unsigned DEFAULT 0 NOT NULL,
          `isHostedFields` tinyint(1) unsigned DEFAULT 0 NOT NULL,
          `date_add` datetime NOT NULL,
          `date_upd` datetime NOT NULL,
          PRIMARY KEY (`id_pscheckout_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');
    $db->execute('
        INSERT INTO `' . _DB_PREFIX_ . 'pscheckout_cart` (`id_cart`, `paypal_order`, `date_add`, `date_upd`)
        SELECT o.id_cart, om.id_order_paypal, o.date_add, o.date_upd
        FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice` AS om
        INNER JOIN `' . _DB_PREFIX_ . 'orders` AS o ON (om.id_order_prestashop = o.id_order)
    ');

    // Restore initial PrestaShop shop context
    if (Shop::CONTEXT_SHOP === $savedShopContext) {
        Shop::setContext($savedShopContext, $savedShopId);
    } elseif (Shop::CONTEXT_GROUP === $savedShopContext) {
        Shop::setContext($savedShopContext, $savedGroupShopId);
    } else {
        Shop::setContext($savedShopContext);
    }

    return true;
}
