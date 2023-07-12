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
 * Update main function for module version 7.3.3.1
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_7_3_3_1($module)
{
    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    $module->registerHook('displayPaymentReturn');
    $module->registerHook('displayOrderDetail');
    $module->registerHook('displayHeader');

    try {
        $db = Db::getInstance();

        // Installing FundingSource if table pscheckout_funding_source is empty or incomplete
        $fundingSources = ['paypal', 'paylater', 'card', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort'];
        $availableFundingSourcesByShops = [];
        $maxPositionByShops = [];
        $availableFundingSources = $db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'pscheckout_funding_source');

        if (!empty($availableFundingSources)) {
            foreach ($availableFundingSources as $availableFundingSource) {
                $currentPosition = (int) $availableFundingSource['position'];
                $shopId = (int) $availableFundingSource['id_shop'];
                if (
                    !isset($maxPositionByShops[$shopId])
                    || $maxPositionByShops[$shopId] < $currentPosition
                ) {
                    $maxPositionByShops[$shopId] = $currentPosition;
                }
                $availableFundingSourcesByShops[$shopId][] = $availableFundingSource['name'];
            }
        }

        foreach (Shop::getShops(false, null, true) as $shopId) {
            $currentPosition = isset($maxPositionByShops[(int) $shopId]) ? $maxPositionByShops[(int) $shopId] + 1 : 1;
            foreach ($fundingSources as $fundingSource) {
                if (!isset($availableFundingSourcesByShops[(int) $shopId]) || !in_array($fundingSource, $availableFundingSourcesByShops[(int) $shopId], true)) {
                    $db->insert(
                        'pscheckout_funding_source',
                        [
                            'name' => pSQL($fundingSource),
                            'active' => 1,
                            'position' => (int) $currentPosition,
                            'id_shop' => (int) $savedShopId,
                        ]
                    );
                    ++$currentPosition;
                }
            }
        }

        // Check module OrderState
        $moduleOrderStates = [
            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT'),
            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT'),
            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT'),
            'PS_CHECKOUT_STATE_AUTHORIZED' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_AUTHORIZED'),
            'PS_CHECKOUT_STATE_PARTIAL_REFUND' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_PARTIAL_REFUND'),
            'PS_CHECKOUT_STATE_WAITING_CAPTURE' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CAPTURE'),
        ];
        $moduleOrderStatesId = array_values($moduleOrderStates);

        $orderStateCollection = new PrestaShopCollection(OrderState::class);
        $orderStateCollection->where('module_name', '=', $module->name);
        $orderStateCollection->where('deleted', '=', '0');

        /** @var OrderState[] $orderStates */
        $orderStates = $orderStateCollection->getResults();
        $currentModuleOrderStatesId = [];

        if (!empty($orderStates)) {
            foreach ($orderStates as $orderState) {
                $orderStateId = (int) $orderState->id;
                if (!in_array($orderStateId, $moduleOrderStatesId, true)) {
                    $orderState->deleted = true;
                    $orderState->save();
                } else {
                    $currentModuleOrderStatesId[] = $orderStateId;
                }
            }
        }

        foreach ($moduleOrderStates as $configuration_key => $id_order_state) {
            if (
                !$id_order_state
                || !in_array((int) $id_order_state, $currentModuleOrderStatesId, true)
            ) {
                switch ($configuration_key) {
                    case 'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT',
                            '#34209E',
                            [
                                'en' => 'Waiting for PayPal payment',
                                'fr' => 'En attente de paiement par PayPal',
                                'es' => 'Esperando el pago con PayPal',
                                'it' => 'In attesa di pagamento con PayPal',
                                'nl' => 'Wachten op PayPal-betaling',
                                'de' => 'Warten auf PayPal-Zahlung',
                                'pl' => 'Oczekiwanie na płatność PayPal',
                                'pt' => 'Aguardando pagamento pelo PayPal',
                            ]
                        );
                        break;
                    case 'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT',
                            '#34209E',
                            [
                                'en' => 'Waiting for Credit Card Payment',
                                'fr' => 'En attente de paiement par Carte de Crédit',
                                'es' => 'Esperando el pago con tarjeta de crédito',
                                'it' => 'In attesa di pagamento con carta di credito',
                                'nl' => 'Wachten op creditcard-betaling',
                                'de' => 'Warten auf Kreditkartenzahlung',
                                'pl' => 'Oczekiwanie na płatność kartą kredytową',
                                'pt' => 'Aguardando pagamento por cartão de crédito',
                            ]
                        );
                        break;
                    case 'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT',
                            '#34209E',
                            [
                                'en' => 'Waiting for Local Payment Method Payment',
                                'fr' => 'En attente de paiement par moyen de paiement local',
                                'es' => 'Esperando el pago con un método de pago local',
                                'it' => 'In attesa di pagamento con metodo di pagamento locale',
                                'nl' => 'Wachten op nlaatselijke betaling',
                                'de' => 'Warten auf Zahlung per lokaler Zahlungsmethode',
                                'pl' => 'Oczekiwanie na płatność lokalnym środkiem płatności',
                                'pt' => 'Aguardando pagamento pelo método de pagamento local',
                            ]
                        );
                        break;
                    case 'PS_CHECKOUT_STATE_AUTHORIZED':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_AUTHORIZED',
                            '#3498D8',
                            [
                                'en' => 'Authorized. To be captured by merchant',
                                'fr' => 'Autorisation. A capturer par le marchand',
                                'es' => 'Autorizado. El vendedor lo capturará',
                                'it' => 'Autorizzato. Sarà acquisito dal commerciante',
                                'nl' => 'Goedgekeurd. Door retailer te registreren.',
                                'de' => 'Autorisiert. Wird von Händler erfasst.',
                                'pl' => 'Pomyślna autoryzacja. Transfer do przeprowadzenia przez sklep',
                                'pt' => 'Autorizado. A ser capturado pelo comerciante',
                            ]
                        );
                        break;
                    case 'PS_CHECKOUT_STATE_PARTIAL_REFUND':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_PARTIAL_REFUND',
                            '#01B887',
                            [
                                'en' => 'Partial refund',
                                'fr' => 'Remboursement partiel',
                                'es' => 'Reembolso parcial',
                                'it' => 'Rimborso parziale',
                                'nl' => 'Gedeeltelijke terugbetaling',
                                'de' => 'Teilweise Rückerstattung',
                                'pl' => 'Częściowy zwrot',
                                'pt' => 'Reembolso parcial',
                            ]
                        );
                        break;
                    case 'PS_CHECKOUT_STATE_WAITING_CAPTURE':
                        ps_checkout_create_order_state_7_3_3_1(
                            'PS_CHECKOUT_STATE_WAITING_CAPTURE',
                            '#3498D8',
                            [
                                'en' => 'Waiting capture',
                                'fr' => 'En attente de capture',
                                'es' => 'Esperando la captura',
                                'it' => 'In attesa di essere acquisito',
                                'nl' => 'Wachten op registratie',
                                'de' => 'Warten auf Erfassung',
                                'pl' => 'Oczekiwanie na transfer',
                                'pt' => 'Aguardando a captura',
                            ]
                        );
                        break;
                }
            }
        }

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
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 3, $exception->getCode(), 'Module', $module->id, false);
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

function ps_checkout_create_order_state_7_3_3_1($configuration_key, $color, $nameByLangIsoCode)
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
