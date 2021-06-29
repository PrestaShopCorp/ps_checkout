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

namespace PrestaShop\Module\PrestashopCheckout\Translations;

class OrderStatesTranslations
{
    const STANDARD_ISO_CODE = 'en';
    const PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT = [
        'en' => 'Waiting for PayPal payment',
        'fr' => 'En attente de paiement par PayPal',
        'es' => 'Esperando el pago con PayPal',
        'it' => 'In attesa di pagamento con PayPal',
        'nl' => 'Wachten op PayPal-betaling',
        'de' => 'Warten auf PayPal-Zahlung',
        'pl' => 'Oczekiwanie na płatność PayPal',
        'pt' => 'Aguardando pagamento pelo PayPal',
    ];
    const PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT = [
        'en' => 'Waiting for Credit Card Payment',
        'fr' => 'En attente de paiement par Carte de Crédit',
        'es' => 'Esperando el pago con tarjeta de crédito',
        'it' => 'In attesa di pagamento con carta di credito',
        'nl' => 'Wachten op creditcard-betaling',
        'de' => 'Warten auf Kreditkartenzahlung',
        'pl' => 'Oczekiwanie na płatność kartą kredytową',
        'pt' => 'Aguardando pagamento por cartão de crédito',
    ];
    const PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT = [
        'en' => 'Waiting for Local Payment Method Payment',
        'fr' => 'En attente de paiement par moyen de paiement local',
        'es' => 'Esperando el pago con un método de pago local',
        'it' => 'In attesa di pagamento con metodo di pagamento locale',
        'nl' => 'Wachten op nlaatselijke betaling',
        'de' => 'Warten auf Zahlung per lokaler Zahlungsmethode',
        'pl' => 'Oczekiwanie na płatność lokalnym środkiem płatności',
        'pt' => 'Aguardando pagamento pelo método de pagamento local',
    ];
    const PS_CHECKOUT_STATE_AUTHORIZED = [
        'en' => 'Authorized. To be captured by merchant',
        'fr' => 'Autorisation. A capturer par le marchand',
        'es' => 'Autorizado. El vendedor lo capturará',
        'it' => 'Autorizzato. Sarà acquisito dal commerciante',
        'nl' => 'Goedgekeurd. Door retailer te registreren.',
        'de' => 'Autorisiert. Wird von Händler erfasst.',
        'pl' => 'Pomyślna autoryzacja. Transfer do przeprowadzenia przez sklep',
        'pt' => 'Autorizado. A ser capturado pelo comerciante',
    ];
    const PS_CHECKOUT_STATE_PARTIAL_REFUND = [
        'en' => 'Partial refund',
        'fr' => 'Remboursement partiel',
        'es' => 'Reembolso parcial',
        'it' => 'Rimborso parziale',
        'nl' => 'Gedeeltelijke terugbetaling',
        'de' => 'Teilweise Rückerstattung',
        'pl' => 'Częściowy zwrot',
        'pt' => 'Reembolso parcial',
    ];
    const PS_CHECKOUT_STATE_WAITING_CAPTURE = [
        'en' => 'Waiting capture',
        'fr' => 'En attente de capture',
        'es' => 'Esperando la captura',
        'it' => 'In attesa di essere acquisito',
        'nl' => 'Wachten op registratie',
        'de' => 'Warten auf Erfassung',
        'pl' => 'Oczekiwanie na transfer',
        'pt' => 'Aguardando a captura',
    ];

    /**
     * Get the States Translations for the table order_state_lang
     *
     * @return array translation list
     */
    public function getTranslations($isoCode)
    {
        $isoCode = $this->confirmIsoCode($isoCode);

        return [
            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_AUTHORIZED' => self::PS_CHECKOUT_STATE_AUTHORIZED[$isoCode],
            'PS_CHECKOUT_STATE_PARTIAL_REFUND' => self::PS_CHECKOUT_STATE_PARTIAL_REFUND[$isoCode],
            'PS_CHECKOUT_STATE_WAITING_CAPTURE' => self::PS_CHECKOUT_STATE_WAITING_CAPTURE[$isoCode],
        ];
    }

    /**
     * Return an ISO which can get a result in the translations arrays
     *
     * @param string $isoCode
     *
     * @return string
     */
    private function confirmIsoCode($isoCode)
    {
        if (!array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_AUTHORIZED) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_PARTIAL_REFUND) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_CAPTURE)) {
            return self::STANDARD_ISO_CODE;
        }

        return (string) $isoCode;
    }
}
