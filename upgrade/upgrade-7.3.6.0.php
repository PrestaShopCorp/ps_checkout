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
 * Update main function for module version 7.3.6.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_3_6_0($module)
{
    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    try {
        $shopsList = Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            // Require the liability shift for all shops
            Configuration::updateValue('PS_CHECKOUT_LIABILITY_SHIFT_REQ', '1', false, null, (int) $shopId);

            // Update global value only if it is not already set to SCA_ALWAYS
            if (Configuration::get('PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES', null, null, $shopId) !== 'SCA_ALWAYS') {
                Configuration::updateValue('PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES', 'SCA_WHEN_REQUIRED', false, null, (int) $shopId);
            }
        }

        // Require the liability shift for all shops
        Configuration::updateGlobalValue('PS_CHECKOUT_LIABILITY_SHIFT_REQ', '1');

        // Update global value only if it is not already set to SCA_ALWAYS
        if (Configuration::getGlobalValue('PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES') !== 'SCA_ALWAYS') {
            Configuration::updateGlobalValue('PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES', 'SCA_WHEN_REQUIRED');
        }

        // Add new configuration for displaying the logo on the product page and the cart
        Configuration::updateGlobalValue('PS_CHECKOUT_DISPLAY_LOGO_PRODUCT', '1');
        Configuration::updateGlobalValue('PS_CHECKOUT_DISPLAY_LOGO_CART', '1');

        // Remove Sofort
        Db::getInstance()->delete(
            'pscheckout_funding_source',
            'name LIKE "sofort"'
        );
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 3, $exception->getCode(), 'Module', $module->id);

        return false;
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
