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
 * Update main function for module version 9.5.3.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_9_5_3_0(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();

        // Check columns in pscheckout_authorization table
        $fields = $db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_authorization`');

        if (!empty($fields)) {
            $hasCreateTime = false;
            $hasUpdateTime = false;
            $hasSellerProtection = false;

            foreach ($fields as $field) {
                if ($field['Field'] === 'create_time') {
                    $hasCreateTime = true;
                }
                if ($field['Field'] === 'update_time') {
                    $hasUpdateTime = true;
                }
                if ($field['Field'] === 'seller_protection') {
                    $hasSellerProtection = true;
                }
            }

            // Add create_time column if it doesn't exist
            if (!$hasCreateTime) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    ADD COLUMN `create_time` varchar(20) NOT NULL DEFAULT ""
                ');
            }

            // Add update_time column if it doesn't exist
            if (!$hasUpdateTime) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    ADD COLUMN `update_time` varchar(20) NOT NULL DEFAULT ""
                ');
            }

            // Drop seller_protection column if it exists
            if ($hasSellerProtection) {
                $db->execute('
                    ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_authorization`
                    DROP COLUMN `seller_protection`
                ');
            }
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    // Install voided order state if not already present
    try {
        $voidedStateId = (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_VOIDED');
        $needsCreation = true;

        if ($voidedStateId) {
            $orderState = new OrderState($voidedStateId);
            if ($orderState->id && $orderState->module_name === $module->name && !$orderState->deleted) {
                $needsCreation = false;
            }
        }

        if ($needsCreation) {
            ps_checkout_create_order_state_9_5_3_0(
                'PS_CHECKOUT_STATE_VOIDED',
                '#DC143C',
                [
                    'en' => 'Authorization voided',
                    'fr' => 'Autorisation annulée',
                    'es' => 'Autorización anulada',
                    'it' => 'Autorizzazione annullata',
                    'nl' => 'Autorisatie ongeldig gemaakt',
                    'de' => 'Autorisierung aufgehoben',
                    'pl' => 'Autoryzacja unieważniona',
                    'pt' => 'Autorização anulada',
                ]
            );
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    $module->registerHook('actionOrderStatusPostUpdate');

    return true;
}

function ps_checkout_create_order_state_9_5_3_0($configuration_key, $color, $nameByLangIsoCode)
{
    $orderStateNameByLangId = [];

    foreach (Language::getLanguages(false) as $language) {
        $languageIsoCode = Tools::strtolower($language['iso_code']);

        if (isset($nameByLangIsoCode[$languageIsoCode])) {
            $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode[$languageIsoCode];
        } elseif (isset($nameByLangIsoCode['en'])) {
            $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode['en'];
        }
    }

    $orderState = new OrderState();
    $orderState->name = $orderStateNameByLangId;
    $orderState->module_name = 'ps_checkout';
    $orderState->unremovable = true;
    $orderState->color = $color;
    $orderState->delivery = false;
    $orderState->shipped = false;
    $orderState->pdf_delivery = false;
    $orderState->pdf_invoice = false;
    $orderState->hidden = false;
    $orderState->invoice = false;
    $orderState->send_email = false;
    $orderState->paid = false;
    $orderState->logable = false;
    $orderState->deleted = false;
    $orderState->template = [];
    $orderState->save();
    Configuration::updateGlobalValue($configuration_key, $orderState->id);
}
