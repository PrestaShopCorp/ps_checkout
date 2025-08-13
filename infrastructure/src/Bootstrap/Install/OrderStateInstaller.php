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

namespace PsCheckout\Infrastructure\Bootstrap\Install;

use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LanguageInterface;

class OrderStateInstaller implements InstallerInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LanguageInterface
     */
    private $languages;

    /**
     * @var string
     */
    private $moduleName;

    public function __construct(ConfigurationInterface $configuration, LanguageInterface $language, $moduleName)
    {
        $this->configuration = $configuration;
        $this->languages = $language;
        $this->moduleName = $moduleName;
    }

    /**
     * {@inheritdoc}
     */
    public function init(): bool
    {
        $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED, $this->configuration->get(OrderStateConfiguration::PS_OS_PAYMENT));
        $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED, $this->configuration->get(OrderStateConfiguration::PS_OS_CANCELED));
        $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR, $this->configuration->get(OrderStateConfiguration::PS_OS_ERROR));
        $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED, $this->configuration->get(OrderStateConfiguration::PS_OS_REFUND));

        if (!$this->checkAlreadyInstalled(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING)) {
            $pendingStateId = $this->createOrderState(
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

            $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING, $pendingStateId);
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED)) {
            $partiallyRefundedStateId = $this->createOrderState(
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

            $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED, $partiallyRefundedStateId);
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID)) {
            $partiallyPaidStateId = $this->createOrderState(
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

            $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID, $partiallyPaidStateId);
        }

        if (!$this->checkAlreadyInstalled(OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED)) {
            $authorizedStateId = $this->createOrderState(
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

            $this->configuration->set(OrderStateConfiguration::PS_CHECKOUT_STATE_AUTHORIZED, $authorizedStateId);
        }

        return true;
    }

    /**
     * @param string $color
     * @param array $nameByLangIsoCode
     *
     * @return int|null
     *
     * @throws \PrestaShopException
     */
    private function createOrderState(string $color, array $nameByLangIsoCode)
    {
        $orderState = new \OrderState();
        $orderState->name = $this->fillOrderStateName($nameByLangIsoCode);
        $orderState->module_name = $this->moduleName;
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

        return $orderState->id;
    }

    /**
     * @param array $nameByLangIsoCode
     *
     * @return array
     */
    private function fillOrderStateName(array $nameByLangIsoCode): array
    {
        $orderStateNameByLangId = [];

        foreach ($this->languages->getAllLanguages() as $language) {
            $languageIsoCode = mb_strtolower($language['iso_code']);

            if (isset($nameByLangIsoCode[$languageIsoCode])) {
                $orderStateNameByLangId[(int) $language['id_lang']] = $nameByLangIsoCode[$languageIsoCode];
            } else {
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
    private function checkAlreadyInstalled(string $orderStateKey): bool
    {
        $orderStateId = $this->configuration->getInteger($orderStateKey);

        if (!$orderStateId) {
            return false;
        }

        $orderState = new \OrderState($orderStateId);

        if (!$orderState->id) {
            return false;
        }

        if ($orderState->module_name !== $this->moduleName || $orderState->deleted) {
            return false;
        }

        return true;
    }
}
