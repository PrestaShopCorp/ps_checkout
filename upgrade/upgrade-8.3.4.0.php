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
    // Force PrestaShop to upgrade for all shop to avoid issues
    $savedShopContext = Shop::getContext();
    $savedShopId = Shop::getContextShopID();
    $savedGroupShopId = Shop::getContextShopGroupID();
    Shop::setContext(Shop::CONTEXT_ALL);

    try {
        $shopsList = Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            Configuration::updateValue('PS_CHECKOUT_LIABILITY_SHIFT_REQ', '0', false, null, (int) $shopId);
        }

        Configuration::updateGlobalValue('PS_CHECKOUT_LIABILITY_SHIFT_REQ', '0');

        $db = Db::getInstance();

        // Check module OrderState
        $moduleOrderStates = [
            'PS_CHECKOUT_STATE_PENDING' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_PENDING'),
            'PS_CHECKOUT_STATE_COMPLETED' => (int) Configuration::getGlobalValue('PS_OS_PAYMENT'),
            'PS_CHECKOUT_STATE_CANCELED' => (int) Configuration::getGlobalValue('PS_OS_CANCELED'),
            'PS_CHECKOUT_STATE_ERROR' => (int) Configuration::getGlobalValue('PS_OS_ERROR'),
            'PS_CHECKOUT_STATE_REFUNDED' => (int) Configuration::getGlobalValue('PS_OS_REFUND'),
            'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_PARTIAL_REFUND'),
            'PS_CHECKOUT_STATE_PARTIALLY_PAID' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_PARTIALLY_PAID'),
            'PS_CHECKOUT_STATE_AUTHORIZED' => (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_AUTHORIZED'),
        ];
        $moduleOrderStatesId = array_values($moduleOrderStates);
        $moduleOrderStatesIdToDelete = [
            (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT'),
            (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT'),
            (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT'),
            (int) Configuration::getGlobalValue('PS_CHECKOUT_STATE_WAITING_CAPTURE'),
        ];

        $orderStateCollection = new PrestaShopCollection(OrderState::class);
        $orderStateCollection->where('module_name', '=', $module->name);
        $orderStateCollection->where('deleted', '=', '0');

        /** @var OrderState[] $orderStates */
        $orderStates = $orderStateCollection->getResults();
        $currentModuleOrderStatesId = [];

        if (!empty($orderStates)) {
            foreach ($orderStates as $orderState) {
                $orderStateId = (int) $orderState->id;
                if (
                    !in_array($orderStateId, $moduleOrderStatesId, true)
                    || in_array($orderState->id, $moduleOrderStatesIdToDelete, true)
                ) {
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
                    case 'PS_CHECKOUT_STATE_AUTHORIZED':
                        ps_checkout_create_order_state_8_3_4_0(
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
                    case 'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED':
                        ps_checkout_create_order_state_8_3_4_0(
                            'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED',
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
                    case 'PS_CHECKOUT_STATE_PENDING':
                        ps_checkout_create_order_state_8_3_4_0('PS_CHECKOUT_STATE_PENDING', '#34209E', [
                            'en' => 'Waiting for payment',
                            'fr' => 'En attente de paiement',
                            'es' => 'Esperando el pago',
                            'it' => 'In attesa di pagamento',
                            'nl' => 'Wachten op betaling',
                            'de' => 'Warten auf Zahlung',
                            'pl' => 'Oczekiwanie na płatność',
                            'pt' => 'Aguardando pagamento',
                        ]);
                        break;
                    case 'PS_CHECKOUT_STATE_PARTIALLY_PAID':
                        ps_checkout_create_order_state_8_3_4_0(
                            'PS_CHECKOUT_STATE_PARTIALLY_PAID',
                            '#3498D8',
                            [
                                'en' => 'Partial payment',
                                'fr' => 'Paiement partiel',
                                'es' => 'Pago parcial',
                                'it' => 'Pagamento parziale',
                                'nl' => 'Gedeeltelijke betaling',
                                'de' => 'Teilweise Zahlung',
                                'pl' => 'Częściowa płatność',
                                'pt' => 'Pagamento parcial',
                            ]
                        );
                        break;
                    default:
                        Configuration::updateGlobalValue($configuration_key, $id_order_state);
                }
            } else {
                Configuration::updateGlobalValue($configuration_key, $id_order_state);
            }
        }

        // Installing FundingSource if table pscheckout_funding_source is empty or incomplete - including BLIK
        $fundingSources = ['paypal', 'paylater', 'card', 'bancontact', 'eps', 'giropay', 'ideal', 'mybank', 'p24', 'sofort', 'blik'];
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

        foreach ($shopsList as $shopId) {
            $currentPosition = isset($maxPositionByShops[(int) $shopId]) ? $maxPositionByShops[(int) $shopId] + 1 : 1;
            foreach ($fundingSources as $fundingSource) {
                if (
                    !isset($availableFundingSourcesByShops[(int) $shopId])
                    || !in_array($fundingSource, $availableFundingSourcesByShops[(int) $shopId], true)
                ) {
                    $db->insert(
                        'pscheckout_funding_source',
                        [
                            'name' => pSQL($fundingSource),
                            'active' => 1,
                            'position' => (int) $currentPosition,
                            'id_shop' => (int) $shopId,
                        ]
                    );
                    ++$currentPosition;
                }
            }
        }
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

function ps_checkout_create_order_state_8_3_4_0($configuration_key, $color, $nameByLangIsoCode)
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
    $orderStateImage = $configuration_key === 'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED' ? 'refund.gif' : 'waiting.gif';
    $moduleOrderStateImgPath = _PS_MODULE_DIR_ . 'ps_checkout/views/img/OrderStatesIcons/' . $orderStateImage;
    $coreOrderStateImgPath = _PS_IMG_DIR_ . 'os/' . $orderState->id . '.gif';

    if (
        Tools::file_exists_cache($moduleOrderStateImgPath)
        && !Tools::file_exists_cache($coreOrderStateImgPath)
        && is_writable(_PS_IMG_DIR_ . 'os/')
    ) {
        Tools::copy($moduleOrderStateImgPath, $coreOrderStateImgPath);
    }
}
