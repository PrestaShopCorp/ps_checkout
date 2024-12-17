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
 * Update main function for module version 7.4.2.2
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_4_2_2($module)
{
    try {
        $result = upgrade_7_4_0_0($module);
        $result &= upgrade_7_4_1_0($module);

        $module->registerHook('moduleRoutes');

        $db = Db::getInstance();
        $shopsList = \Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            $hasFundingSourceApplePay = (bool) $db->getValue('
                SELECT 1
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `name` = "apple_pay"
                AND `id_shop` = ' . (int) $shopId
            );

            if (!$hasFundingSourceApplePay) {
                $db->insert(
                    'pscheckout_funding_source',
                    [
                        'name' => 'apple_pay',
                        'position' => 12,
                        'active' => 0,
                        'id_shop' => (int) $shopId,
                    ]
                );
            }
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return $result;
}

/**
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_7_4_0_0($module)
{
    try {
        $db = Db::getInstance();
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_order` (
            `id` varchar(50) NOT NULL,
            `id_cart` int unsigned NOT NULL,
            `status` varchar(30) NOT NULL,
            `intent` varchar(50) DEFAULT "CAPTURE",
            `funding_source` varchar(50) NOT NULL,
            `payment_source` text,
            `environment` varchar(50) NOT NULL,
            `is_card_fields` tinyint(1) NOT NULL,
            `is_express_checkout` tinyint(1) NOT NULL,
            `customer_intent` varchar(50),
            `payment_token_id` varchar(50),
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_capture` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `final_capture` tinyint(1) NOT NULL,
            `created_at` varchar(50) NOT NULL,
            `updated_at` varchar(50) NOT NULL,
            `seller_protection` text,
            `seller_receivable_breakdown` text,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_refund` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `invoice_id` varchar(50) NOT NULL,
            `custom_id` varchar(50) NOT NULL,
            `acquirer_reference_number` varchar(50) NOT NULL,
            `seller_payable_breakdown` text,
            `id_order_slip` INT UNSIGNED,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_authorization` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `expiration_time` varchar(50) NOT NULL,
            `seller_protection` text,
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
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_customer` (
            `id_customer` int unsigned NOT NULL,
            `paypal_customer_id` varchar(50) NOT NULL,
            PRIMARY KEY (`id_customer`, `paypal_customer_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_payment_token` (
            `id` INT UNSIGNED AUTO_INCREMENT,
            `token_id` varchar(50) NOT NULL,
            `paypal_customer_id` varchar(50) NOT NULL,
            `payment_source` varchar(50) NOT NULL,
            `data` text NOT NULL,
            `merchant_id` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `is_favorite` tinyint(1) unsigned DEFAULT 0 NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `token_id_merchant_id_paypal_customer_id` (`token_id`, `merchant_id`, `paypal_customer_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');
        $db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` CHANGE `paypal_status` `paypal_status` VARCHAR(30) NULL; ');
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}

/**
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_7_4_1_0($module)
{
    try {
        $db = Db::getInstance();
        $shopsList = \Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            $isGooglePayEligible = (bool) \Configuration::get(
                'PS_CHECKOUT_GOOGLE_PAY',
                null,
                null,
                $shopId
            );
            $hasFundingSourceGooglePay = (bool) $db->getValue('
                SELECT 1
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `name` = "google_pay"
                AND `id_shop` = ' . (int) $shopId
            );

            if (!$hasFundingSourceGooglePay) {
                $db->insert(
                    'pscheckout_funding_source',
                    [
                        'name' => 'google_pay',
                        'position' => 11,
                        'active' => (int) $isGooglePayEligible,
                        'id_shop' => (int) $shopId,
                    ]
                );
            }
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
