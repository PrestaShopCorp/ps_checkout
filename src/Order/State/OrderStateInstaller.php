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

namespace PrestaShop\Module\PrestashopCheckout\Order\State;

use Configuration;
use Language;
use OrderState;
use Tools;
use Validate;

class OrderStateInstaller
{
    /**
     * @var array
     */
    private $languages;

    public function __construct()
    {
        $this->languages = Language::getLanguages(false);
    }

    public function install()
    {
        Configuration::updateGlobalValue(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED, Configuration::getGlobalValue(OrderStateConfigurationKeys::PS_OS_PAYMENT));
        Configuration::updateGlobalValue(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED, Configuration::getGlobalValue(OrderStateConfigurationKeys::PS_OS_CANCELED));
        Configuration::updateGlobalValue(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_ERROR, Configuration::getGlobalValue(OrderStateConfigurationKeys::PS_OS_ERROR));
        Configuration::updateGlobalValue(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED, Configuration::getGlobalValue(OrderStateConfigurationKeys::PS_OS_REFUND));

        if (!$this->checkAlreadyInstalled(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING)) {
            $this->createOrderState(
                OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING,
                '#34209E',
                [
                    'en' => 'Waiting for payment',
                    'fr' => 'En attente de paiement',
                    'es' => 'Esperando el pago',
                    'it' => 'In attesa di pagamento',
                    'nl' => 'Wachten op betaling',
                    'de' => 'Warten auf Zahlung',
                    'pl' => 'Oczekiwanie na płatność',
                    'pt' => 'Aguardando pagamento',
                ]
            );
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED)) {
            $this->createOrderState(
                OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED,
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
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID)) {
            $this->createOrderState(
                OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID,
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
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED)) {
            $this->createOrderState(
                OrderStateConfigurationKeys::PS_CHECKOUT_STATE_AUTHORIZED,
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
        }
    }

    /**
     * @param string $configuration_key
     * @param string $color
     * @param array $nameByLangIsoCode
     */
    private function createOrderState($configuration_key, $color, array $nameByLangIsoCode)
    {
        $orderState = new OrderState();
        $orderState->name = $this->fillOrderStateName($nameByLangIsoCode);
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
        $this->setStateIcons($configuration_key, $orderState->id);
    }

    /**
     * @param array $nameByLangIsoCode
     *
     * @return array
     */
    private function fillOrderStateName(array $nameByLangIsoCode)
    {
        $orderStateNameByLangId = [];

        foreach ($this->languages as $language) {
            $languageIsoCode = Tools::strtolower($language['iso_code']);

            if (isset($nameByLangIsoCode[$languageIsoCode])) {
                $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode[$languageIsoCode];
            } elseif (isset($nameByLangIsoCode['en'])) {
                $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode['en'];
            }
        }

        return $orderStateNameByLangId;
    }

    /**
     * @param string $orderStateKey
     *
     * @return bool
     */
    private function checkAlreadyInstalled($orderStateKey)
    {
        $orderStateId = (int) Configuration::getGlobalValue($orderStateKey);

        if (!$orderStateId) {
            return false;
        }

        $orderState = new OrderState($orderStateId);

        if (!Validate::isLoadedObject($orderState)) {
            return false;
        }

        if ($orderState->module_name !== 'ps_checkout' || $orderState->deleted) {
            return false;
        }

        return true;
    }

    /**
     * @param string $orderStateKey
     * @param int $orderStateId
     */
    private function setStateIcons($orderStateKey, $orderStateId)
    {
        $orderStateImage = $orderStateKey === OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED ? 'refund.gif' : 'waiting.gif';
        $moduleOrderStateImgPath = _PS_MODULE_DIR_ . 'ps_checkout/views/img/OrderStatesIcons/' . $orderStateImage;
        $coreOrderStateImgPath = _PS_IMG_DIR_ . 'os/' . $orderStateId . '.gif';

        if (
            Tools::file_exists_cache($moduleOrderStateImgPath)
            && !Tools::file_exists_cache($coreOrderStateImgPath)
            && is_writable(_PS_IMG_DIR_ . 'os/')
        ) {
            Tools::copy($moduleOrderStateImgPath, $coreOrderStateImgPath);
        }
    }
}
