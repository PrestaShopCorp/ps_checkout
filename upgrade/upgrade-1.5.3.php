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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module Version 1.5.3
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_1_5_3($module)
{
    Configuration::deleteByName('PS_CHECKOUT_PAYPAL_ID_MERCHANT');
    Configuration::deleteByName('PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT');
    Configuration::deleteByName('PS_CHECKOUT_PAYPAL_EMAIL_STATUS');
    Configuration::deleteByName('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS');
    Configuration::deleteByName('PS_CHECKOUT_CARD_PAYMENT_STATUS');
    Configuration::deleteByName('PS_PSX_FIREBASE_EMAIL');
    Configuration::deleteByName('PS_PSX_FIREBASE_ID_TOKEN');
    Configuration::deleteByName('PS_PSX_FIREBASE_LOCAL_ID');
    Configuration::deleteByName('PS_PSX_FIREBASE_REFRESH_TOKEN');
    Configuration::deleteByName('PS_PSX_FIREBASE_REFRESH_DATE');
    Configuration::deleteByName('PS_CHECKOUT_SHOP_UUID_V4');
    Configuration::deleteByName('PS_CHECKOUT_PSX_FORM');

    $shopUuidManager = new PrestaShop\Module\PrestashopCheckout\ShopUuidManager();

    return $shopUuidManager->generateForAllShops();
}
