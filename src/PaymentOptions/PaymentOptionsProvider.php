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

/**
 * Class PaymentOptionsProvider is used to define default payment options
 */
class PaymentOptionsProvider
{
    public function createDefaultPaymentOptions()
    {
        // Create all the payment method available
        return new PaymentOptions([
            $this->getCreditCardOption(),
            $this->getPaypalOption(),
            $this->getBanContactOption(),
            $this->getIdealOption(),
            $this->getGiropayOption(),
            $this->getEPSOption(),
            $this->getMyBankOption(),
            $this->getSofortOption(),
            $this->getP24Option(),
        ]);
    }

    private function getCreditCardOption()
    {
        return new PaymentOption('card', 0);
    }

    private function getPaypalOption()
    {
        return new PaymentOption('paypal', 1, 'paypal-logo-thumbnail.png');
    }

    private function getBanContactOption()
    {
        $bancontact = new PaymentOption('bancontact', 2, 'bancontact_logo.svg');
        $bancontact->setCountriesByIsoCode(['be']);

        return $bancontact;
    }

    private function getIdealOption()
    {
        $ideal = new PaymentOption('ideal', 3, 'ideal_logo.svg');
        $ideal->setCountriesByIsoCode(['nl']);

        return $ideal;
    }

    private function getGiropayOption()
    {
        $giropay = new PaymentOption('gyropay', 4, 'giropay_logo.svg');
        $giropay->setCountriesByIsoCode(['de']);

        return $giropay;
    }

    private function getEPSOption()
    {
        $eps = new PaymentOption('eps', 5, 'eps_logo.svg');
        $eps->setCountriesByIsoCode(['at']);

        return $eps;
    }

    private function getMyBankOption()
    {
        $myBank = new PaymentOption('mybank', 6, 'mybank_logo.svg');
        $myBank->setCountriesByIsoCode(['it']);

        return $myBank;
    }

    private function getSofortOption()
    {
        $sofort = new PaymentOption('sofort', 7, 'sofort_logo.svg');
        $sofort->setCountriesByIsoCode(['be', 'es', 'it', 'de', 'nl', 'at']);

        return $sofort;
    }

    private function getP24Option()
    {
        $p24 = new PaymentOption('p24', 8, 'p24_logo.svg');
        $p24->setCountriesByIsoCode(['pl']);

        return $p24;
    }
}
