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
use PrestaShop\Module\PrestashopCheckout\OrderStates;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module Version 1.2.10
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_2_10($module)
{
    foreach (OrderStates::ORDER_STATES as $key => $value) {
        $idState = \Configuration::getGlobalValue($key);

        $update = \Db::getInstance()->update(
            OrderStates::ORDER_STATE_TABLE,
            ['color' => $value],
            'module_name = "ps_checkout" AND id_order_state = ' . (int) $idState
        );

        if ($update !== true) {
            return false;
        }
    }

    $shopsList = \Shop::getShops(false, null, true);

    foreach ($shopsList as $shopId) {
        \Configuration::updateValue(
            'PS_CHECKOUT_CARD_PAYMENT_ENABLED',
            true,
            false,
            null,
            (int) \Context::getContext()->shop->id
        );

        // New configurations for express checkout feature
        \Configuration::updateValue(
            'PS_CHECKOUT_EC_ORDER_PAGE',
            false,
            false,
            null,
            (int) \Context::getContext()->shop->id
        );
        \Configuration::updateValue(
            'PS_CHECKOUT_EC_CHECKOUT_PAGE',
            false,
            false,
            null,
            (int) \Context::getContext()->shop->id
        );
        \Configuration::updateValue(
            'PS_CHECKOUT_EC_PRODUCT_PAGE',
            false,
            false,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

    // register new hooks for express checkout
    $hooks = [
        'displayExpressCheckout',
        'DisplayFooterProduct',
        'displayPersonalInformationTop',
        'actionBeforeCartUpdateQty',
        'header',
    ];

    $module->registerHook($hooks);

    return true;
}
