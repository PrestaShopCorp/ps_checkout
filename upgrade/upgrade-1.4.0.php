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

    // Fix multiple OrderState created in multishop before 1.3.0
    $queryConfigurationResults = $db->executeS('
        SELECT c.id_configuration, c.name, c.value, c.id_shop, c.id_shop_group, os.id_order_state
        FROM `' . _DB_PREFIX_ . 'configuration` AS c
        LEFT JOIN `' . _DB_PREFIX_ . 'order_state` AS os ON (c.value = os.id_order_state)
        WHERE c.name LIKE "PS_CHECKOUT_STATE_%"
    ');

    $orderStatesToClean = [
        'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT',
        'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT',
        'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT',
        'PS_CHECKOUT_STATE_AUTHORIZED',
        'PS_CHECKOUT_STATE_PARTIAL_REFUND',
        'PS_CHECKOUT_STATE_WAITING_CAPTURE',
    ];
    $orderStateRows = [];

    if (false === empty($queryConfigurationResults)) {
        foreach ($queryConfigurationResults as $queryConfigurationResult) {
            if (false === in_array($queryConfigurationResult['name'], $orderStatesToClean, true)) {
                continue;
            }

            $orderStateRows[$queryConfigurationResult['name']][] = [
                'id_configuration' => $queryConfigurationResult['id_configuration'],
                'id_order_state' => $queryConfigurationResult['id_order_state'],
            ];
        }
    }

    foreach ($orderStateRows as $orderStateRow) {
        $isGlobalValueSaved = false;
        foreach ($orderStateRow as $index => $data) {
            if (false === empty($data['id_order_state'])) {
                if (false === $isGlobalValueSaved) {
                    // Set value global for all shops
                    $result = $db->update(
                        'configuration',
                        [
                            'id_shop' => null,
                            'id_shop_group' => null,
                        ],
                        'id_configuration = ' . (int) $data['id_configuration'],
                        0,
                         true
                    );

                    if ($result) {
                        $isGlobalValueSaved = true;
                        // Skip deletion of this configuration
                        continue;
                    }
                } else {
                    // Mark this duplicated OrderState as deleted
                    $db->update(
                        'order_state',
                        [
                            'deleted' => 1,
                        ],
                        'id_order_state = ' . (int) $data['id_order_state']
                    );
                }
            }

            // Remove this OrderState identifier from Configuration
            $db->delete(
                'configuration',
                'id_configuration = ' . (int) $data['id_configuration']
            );
        }
    }

    // Mark OrderState created by older module installation who failed as deleted
    $queryOrderState = new \DbQuery();
    $queryOrderState->select('id_order_state');
    $queryOrderState->from('order_state');
    $queryOrderState->where('module_name = "' . $module->name . '"');
    $queryOrderState->where('deleted = 0');

    if (false === empty($queryConfigurationResults)) {
        $queryOrderState->where('`id_order_state` NOT IN (' . implode(',', array_column($queryConfigurationResults, 'id_order_state')) . ')');
    }

    $queryOrderStateResults = $db->executeS($queryOrderState);

    if (false === empty($queryOrderStateResults)) {
        foreach ($queryOrderStateResults as $queryOrderStateResult) {
            $db->update(
                'order_state',
                [
                    'deleted' => 1,
                ],
                'id_order_state = ' . (int) $queryOrderStateResult['id_order_state']
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
