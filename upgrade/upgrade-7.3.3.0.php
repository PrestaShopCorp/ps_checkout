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
 * Update main function for module version 7.3.3.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_3_3_0($module)
{
    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    $module->registerHook('displayPaymentReturn');
    $module->registerHook('displayOrderDetail');
    $module->registerHook('displayHeader');

    // Installing FundingSource if table pscheckout_funding_source is empty
    try {
        $db = Db::getInstance();
        if (!$db->getValue("SELECT COUNT(*) FROM ". _DB_PREFIX_ ."'pscheckout_funding_source'")) {
            $db->insert(
                'pscheckout_funding_source',
                [
                    ['name' => 'paypal', 'active' => 1, 'position' => 1, 'id_shop' => $savedShopId],
                    ['name' => 'paylater', 'active' => 1, 'position' => 2, 'id_shop' => $savedShopId],
                    ['name' => 'card', 'active' => 1, 'position' => 3, 'id_shop' => $savedShopId],
                    ['name' => 'bancontact', 'active' => 1, 'position' => 4, 'id_shop' => $savedShopId],
                    ['name' => 'eps', 'active' => 1, 'position' => 5, 'id_shop' => $savedShopId],
                    ['name' => 'giropay', 'active' => 1, 'position' => 6, 'id_shop' => $savedShopId],
                    ['name' => 'ideal', 'active' => 1, 'position' => 7, 'id_shop' => $savedShopId],
                    ['name' => 'mybank', 'active' => 1, 'position' => 8, 'id_shop' => $savedShopId],
                    ['name' => 'p24', 'active' => 1, 'position' => 9, 'id_shop' => $savedShopId],
                    ['name' => 'sofort', 'active' => 1, 'position' => 10, 'id_shop' => $savedShopId],
                ]
            );
        }
    } catch (Exception $e) {
    }

    try {
        $db = Db::getInstance();
        $db->delete(
            'pscheckout_cart',
            'paypal_order IS NULL'
        );
        $db->update(
            'pscheckout_cart',
            [
                'paypal_token' => null,
                'paypal_token_expire' => null,
            ],
            'paypal_token IS NOT NULL',
            0,
            true
        );
    } catch (Exception $e) {
    }

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
