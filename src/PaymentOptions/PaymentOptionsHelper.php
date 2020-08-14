<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\PaymentOptions;

class PaymentOptionsHelper
{
    public static function initPaymentOptions()
    {
        // Create all the payment method available
        $creditCard = new PaymentOption('card', 0);
        $paypal = new PaymentOption('paypal', 1, 'paypal-logo-thumbnail.png');
        $bancontact = new PaymentOption('bancontact', 2, 'bancontact_logo.png');
        $bancontact->setCountriesByIsoCode(['be']);
        $ideal = new PaymentOption('ideal', 3, 'ideal_logo.png');
        $ideal->setCountriesByIsoCode(['nl']);
        $giropay = new PaymentOption('gyropay', 4, 'giropay_logo.png');
        $giropay->setCountriesByIsoCode(['de']);
        $eps = new PaymentOption('eps', 5, 'eps_logo.png');
        $eps->setCountriesByIsoCode(['at']);
        $myBank = new PaymentOption('mybank', 6, 'mybank_logo.png');
        $myBank->setCountriesByIsoCode(['it']);
        $sofort = new PaymentOption('sofort', 7, 'sofort_logo.png');
        $sofort->setCountriesByIsoCode(['be', 'es', 'it', 'de', 'nl', 'at']);
        $p24 = new PaymentOption('p24', 8, 'p24_logo.png');
        $p24->setCountriesByIsoCode(['pl']);

        return new PaymentOptions([
            $creditCard,
            $paypal,
            $bancontact,
            $ideal,
            $giropay,
            $eps,
            $myBank,
            $sofort,
            $p24,
        ]);
    }

    /**
     * @param array $paymentOptionsFromConfig
     *
     * @return PaymentOptions
     */
    public static function decodePaymentOptionsFromConfig($paymentOptionsFromConfig)
    {
        $paymentOptions = new PaymentOptions();
        foreach ($paymentOptionsFromConfig as $index => $paymentOption) {
            $payment = new PaymentOption($paymentOption['name'], $index, $paymentOption['logo'], $paymentOption['enabled']);
            $payment->setCountries($paymentOption['countries']);
            $paymentOptions->addPaymentOption($payment);
        }

        return $paymentOptions;
    }

    public static function decodePaymentOptionsFromAjax($paymentOptionsFromAjax)
    {
        $paymentOptions = new PaymentOptions();
        foreach ($paymentOptionsFromAjax as $index => $paymentOption) {
            $payment = new PaymentOption($paymentOption['name'], $index, $paymentOption['logo'], $paymentOption['enabled']);
            $payment->setCountriesByName($paymentOption['countries']);
            $paymentOptions->addPaymentOption($payment);
        }

        return $paymentOptions;
    }
}
