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
 * Update main function for module version 3.0.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_3_0_0($module)
{
    $db = Db::getInstance();
    $result = true;

    // Mark as business data check
    foreach (\Shop::getShops(false, null, true) as $shopId) {
        $isFirebaseOnboarded = (bool) Configuration::get(
            'PS_PSX_FIREBASE_ID_TOKEN',
            null,
            null,
            (int) $shopId
        );
        $shopDataCollected = (bool) Configuration::get(
            'PS_CHECKOUT_PSX_FORM',
            null,
            null,
            (int) $shopId
        );
        $psCheckoutOnboarded = $isFirebaseOnboarded() && $shopDataCollected();
        $businessDataCheck = $psCheckoutOnboarded ? 1 : 0;

        $result = $result &&
            (bool) Configuration::updateValue(
                'PS_CHECKOUT_BUSINESS_DATA_CHECK',
                $businessDataCheck,
                false,
                null,
                (int) $shopId
            ) &&
            (bool) Configuration::updateValue(
                'PS_CHECKOUT_DISPLAY_DATA_CHECK_MSG',
                1,
                false,
                null,
                (int) $shopId
            );
    }

    // Create session tables
    return $result && $db->execute('
        CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'pscheckout_onboarding_session (
            correlation_id VARCHAR(255) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            shop_id INT NOT NULL,
            is_closed INT NOT NULL,
            auth_token VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            closed_at DATETIME,
            expires_at DATETIME,
            is_sse_opened TINYINT(1) NOT NULL DEFAULT 0,
            data TEXT,
            PRIMARY KEY (mode, shop_id, is_closed)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ') && $db->execute('
        CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'pscheckout_payment_session (
            correlation_id VARCHAR(255) NOT NULL,
            mode VARCHAR(255) NOT NULL,
            user_id INT NOT NULL,
            shop_id INT NOT NULL,
            is_closed INT NOT NULL,
            auth_token VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            closed_at DATETIME,
            expires_at DATETIME,
            is_sse_opened TINYINT(1) NOT NULL DEFAULT 0,
            data TEXT,
            PRIMARY KEY (mode, user_id, shop_id, is_closed)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');
}
