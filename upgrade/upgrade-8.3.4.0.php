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
 * Update main function for module version 8.3.4.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_8_3_4_0($module)
{
    $orderStateCollection = new PrestaShopCollection(OrderState::class);
    $orderStateCollection->where('module_name', '=', $module->name);
    $orderStateCollection->where('deleted', '=', '0');

    /** @var OrderState[] $orderStates */
    $orderStates = $orderStateCollection->getResults();

    if (!empty($orderStates)) {
        foreach ($orderStates as $orderState) {
            if (in_array($orderState->id, [Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT'), Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT'), Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT')])) {
                $orderState->deleted = true;
                $orderState->save();
            }
        }
    }

    ps_checkout_create_order_state_8_3_4_0('PS_CHECKOUT_STATE_WAITING_PAYMENT', '#34209E', [
        'en' => 'Waiting for payment',
        'fr' => 'En attente de paiement',
        'es' => 'Esperando el pago',
        'it' => 'In attesa di pagamento',
        'nl' => 'Wachten op betaling',
        'de' => 'Warten auf Zahlung',
        'pl' => 'Oczekiwanie na płatność',
        'pt' => 'Aguardando pagamento pelo',
    ]);

    return true;
}

function ps_checkout_create_order_state_8_3_4_0($configuration_key, $color, $nameByLangIsoCode)
{
    $orderStateNameByLangId = [];
    foreach ($nameByLangIsoCode as $langIsoCode => $name) {
        foreach (Language::getLanguages(false) as $language) {
            if (Tools::strtolower($language['iso_code']) === $langIsoCode) {
                $orderStateNameByLangId[(int) $language['id_lang']] = $name;
            } elseif (isset($nameByLangIsoCode['en'])) {
                $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode['en'];
            }
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
