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
 * Update main function for module Version 1.4.0
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_4_0($module)
{
    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    $db = Db::getInstance();

    // Remove our ModuleAdminControllers from SEO & URLs page
    $queryMeta = new DbQuery();
    $queryMeta->select('id_meta');
    $queryMeta->from('meta');
    $queryMeta->where('page LIKE "module-' . $module->name . '-Admin%"');
    $queryMetaResults = $db->executeS($queryMeta);

    if (false === empty($queryMetaResults)) {
        foreach ($queryMetaResults as $queryMetaResult) {
            $db->delete(
                'meta',
                'id_meta = ' . (int) $queryMetaResult['id_meta']
            );

            $db->delete(
                'meta_lang',
                'id_meta = ' . (int) $queryMetaResult['id_meta']
            );
        }
    }

    $module->registerHook('displayAdminOrderLeft');
    $module->registerHook('displayAdminOrderMainBottom');
    $module->registerHook('actionAdminControllerSetMedia');
    $module->unregisterHook('actionOrderSlipAdd');
    $module->unregisterHook('actionOrderStatusUpdate');

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
