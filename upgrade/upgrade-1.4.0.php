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
 * Update main function for module Version 1.4.0
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_4_0($module)
{
    $result = true;
    $db = Db::getInstance();

    // Remove our ModuleAdminControllers from SEO & URLs page
    $queryMeta = new DbQuery();
    $queryMeta->select('id_meta');
    $queryMeta->from('meta');
    $queryMeta->where('page LIKE "module-' . $module->name . '-Admin%"');
    $queryMetaResults = $db->executeS($queryMeta);

    if (false === empty($queryMetaResults)) {
        foreach ($queryMetaResults as $queryMetaResult) {
            $result = $result && (bool) $db->delete(
                'meta',
                'id_meta = ' . (int) $queryMetaResult['id_meta']
            );

            $result = $result && (bool) $db->delete(
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
                    $result = $result && $db->update(
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
                    $result = $result && $db->update(
                        'order_state',
                        [
                            'deleted' => 1,
                        ],
                        'id_order_state = ' . (int) $data['id_order_state']
                    );
                }
            }

            // Remove this OrderState identifier from Configuration
            $result = $result && $db->delete(
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
            $result = $result && $db->update(
                'order_state',
                [
                    'deleted' => 1,
                ],
                'id_order_state = ' . (int) $queryOrderStateResult['id_order_state']
            );
        }
    }

    return $result
        && (bool) $module->registerHook('displayAdminOrderLeft')
        && (bool) $module->registerHook('displayAdminOrderMainBottom')
        && (bool) $module->registerHook('actionAdminControllerSetMedia')
        && (bool) $module->unregisterHook('actionOrderSlipAdd')
        && (bool) $module->unregisterHook('actionOrderStatusUpdate');
}
