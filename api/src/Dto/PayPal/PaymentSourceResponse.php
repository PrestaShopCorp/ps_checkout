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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The payment source used to fund the payment.
 */
class PaymentSourceResponse
{
    /**
     * @var CardResponse|null
     */
    private $card;

    /**
     * @var PaypalWalletResponse|null
     */
    private $paypal;

    /**
     * @var BancontactPaymentObject|null
     */
    private $bancontact;

    /**
     * @var BlikPaymentObject|null
     */
    private $blik;

    /**
     * @var EpsPaymentObject|null
     */
    private $eps;

    /**
     * @var GiropayPaymentObject|null
     */
    private $giropay;

    /**
     * @var IdealPaymentObject|null
     */
    private $ideal;

    /**
     * @var MybankPaymentObject|null
     */
    private $mybank;

    /**
     * @var P24PaymentObject|null
     */
    private $p24;

    /**
     * @var SofortPaymentObject|null
     */
    private $sofort;

    /**
     * @var TrustlyPaymentObject|null
     */
    private $trustly;

    /**
     * @var ApplePayPaymentObject|null
     */
    private $applePay;

    /**
     * @var GooglePayWalletResponse|null
     */
    private $googlePay;

    /**
     * @var VenmoWalletResponse|null
     */
    private $venmo;

    /**
     * Returns Card.
     * The payment card to use to fund a payment. Card can be a credit or debit card.
     */
    public function getCard(): ?CardResponse
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * The payment card to use to fund a payment. Card can be a credit or debit card.
     *
     * @maps card
     * @return self
     */
    public function setCard(?CardResponse $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Returns Paypal.
     * The PayPal Wallet response.
     */
    public function getPaypal(): ?PaypalWalletResponse
    {
        return $this->paypal;
    }

    /**
     * Sets Paypal.
     * The PayPal Wallet response.
     *
     * @maps paypal
     * @return self
     */
    public function setPaypal(?PaypalWalletResponse $paypal): self
    {
        $this->paypal = $paypal;

        return $this;
    }

    /**
     * Returns Bancontact.
     * Information used to pay Bancontact.
     */
    public function getBancontact(): ?BancontactPaymentObject
    {
        return $this->bancontact;
    }

    /**
     * Sets Bancontact.
     * Information used to pay Bancontact.
     *
     * @maps bancontact
     * @return self
     */
    public function setBancontact(?BancontactPaymentObject $bancontact): self
    {
        $this->bancontact = $bancontact;

        return $this;
    }

    /**
     * Returns Blik.
     * Information used to pay using BLIK.
     */
    public function getBlik(): ?BlikPaymentObject
    {
        return $this->blik;
    }

    /**
     * Sets Blik.
     * Information used to pay using BLIK.
     *
     * @maps blik
     * @return self
     */
    public function setBlik(?BlikPaymentObject $blik): self
    {
        $this->blik = $blik;

        return $this;
    }

    /**
     * Returns Eps.
     * Information used to pay using eps.
     */
    public function getEps(): ?EpsPaymentObject
    {
        return $this->eps;
    }

    /**
     * Sets Eps.
     * Information used to pay using eps.
     *
     * @maps eps
     * @return self
     */
    public function setEps(?EpsPaymentObject $eps): self
    {
        $this->eps = $eps;

        return $this;
    }

    /**
     * Returns Giropay.
     * Information needed to pay using giropay.
     */
    public function getGiropay(): ?GiropayPaymentObject
    {
        return $this->giropay;
    }

    /**
     * Sets Giropay.
     * Information needed to pay using giropay.
     *
     * @maps giropay
     * @return self
     */
    public function setGiropay(?GiropayPaymentObject $giropay): self
    {
        $this->giropay = $giropay;

        return $this;
    }

    /**
     * Returns Ideal.
     * Information used to pay using iDEAL.
     */
    public function getIdeal(): ?IdealPaymentObject
    {
        return $this->ideal;
    }

    /**
     * Sets Ideal.
     * Information used to pay using iDEAL.
     *
     * @maps ideal
     * @return self
     */
    public function setIdeal(?IdealPaymentObject $ideal): self
    {
        $this->ideal = $ideal;

        return $this;
    }

    /**
     * Returns Mybank.
     * Information used to pay using MyBank.
     */
    public function getMybank(): ?MybankPaymentObject
    {
        return $this->mybank;
    }

    /**
     * Sets Mybank.
     * Information used to pay using MyBank.
     *
     * @maps mybank
     * @return self
     */
    public function setMybank(?MybankPaymentObject $mybank): self
    {
        $this->mybank = $mybank;

        return $this;
    }

    /**
     * Returns P 24.
     * Information used to pay using P24(Przelewy24).
     */
    public function getP24(): ?P24PaymentObject
    {
        return $this->p24;
    }

    /**
     * Sets P 24.
     * Information used to pay using P24(Przelewy24).
     *
     * @maps p24
     * @return self
     */
    public function setP24(?P24PaymentObject $p24): self
    {
        $this->p24 = $p24;

        return $this;
    }

    /**
     * Returns Sofort.
     * Information used to pay using Sofort.
     */
    public function getSofort(): ?SofortPaymentObject
    {
        return $this->sofort;
    }

    /**
     * Sets Sofort.
     * Information used to pay using Sofort.
     *
     * @maps sofort
     * @return self
     */
    public function setSofort(?SofortPaymentObject $sofort): self
    {
        $this->sofort = $sofort;

        return $this;
    }

    /**
     * Returns Trustly.
     * Information needed to pay using Trustly.
     */
    public function getTrustly(): ?TrustlyPaymentObject
    {
        return $this->trustly;
    }

    /**
     * Sets Trustly.
     * Information needed to pay using Trustly.
     *
     * @maps trustly
     * @return self
     */
    public function setTrustly(?TrustlyPaymentObject $trustly): self
    {
        $this->trustly = $trustly;

        return $this;
    }

    /**
     * Returns Apple Pay.
     * Information needed to pay using ApplePay.
     */
    public function getApplePay(): ?ApplePayPaymentObject
    {
        return $this->applePay;
    }

    /**
     * Sets Apple Pay.
     * Information needed to pay using ApplePay.
     *
     * @maps apple_pay
     * @return self
     */
    public function setApplePay(?ApplePayPaymentObject $applePay): self
    {
        $this->applePay = $applePay;

        return $this;
    }

    /**
     * Returns Google Pay.
     * Google Pay Wallet payment data.
     */
    public function getGooglePay(): ?GooglePayWalletResponse
    {
        return $this->googlePay;
    }

    /**
     * Sets Google Pay.
     * Google Pay Wallet payment data.
     *
     * @maps google_pay
     * @return self
     */
    public function setGooglePay(?GooglePayWalletResponse $googlePay): self
    {
        $this->googlePay = $googlePay;

        return $this;
    }

    /**
     * Returns Venmo.
     * Venmo wallet response.
     */
    public function getVenmo(): ?VenmoWalletResponse
    {
        return $this->venmo;
    }

    /**
     * Sets Venmo.
     * Venmo wallet response.
     *
     * @maps venmo
     * @return self
     */
    public function setVenmo(?VenmoWalletResponse $venmo): self
    {
        $this->venmo = $venmo;

        return $this;
    }
}
