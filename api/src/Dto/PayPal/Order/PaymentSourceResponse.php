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

namespace PsCheckout\Api\Dto\PayPal\Order;

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
     */
    public function setCard(?CardResponse $card): void
    {
        $this->card = $card;
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
     */
    public function setPaypal(?PaypalWalletResponse $paypal): void
    {
        $this->paypal = $paypal;
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
     */
    public function setBancontact(?BancontactPaymentObject $bancontact): void
    {
        $this->bancontact = $bancontact;
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
     */
    public function setBlik(?BlikPaymentObject $blik): void
    {
        $this->blik = $blik;
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
     */
    public function setEps(?EpsPaymentObject $eps): void
    {
        $this->eps = $eps;
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
     */
    public function setGiropay(?GiropayPaymentObject $giropay): void
    {
        $this->giropay = $giropay;
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
     */
    public function setIdeal(?IdealPaymentObject $ideal): void
    {
        $this->ideal = $ideal;
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
     */
    public function setMybank(?MybankPaymentObject $mybank): void
    {
        $this->mybank = $mybank;
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
     */
    public function setP24(?P24PaymentObject $p24): void
    {
        $this->p24 = $p24;
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
     */
    public function setSofort(?SofortPaymentObject $sofort): void
    {
        $this->sofort = $sofort;
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
     */
    public function setTrustly(?TrustlyPaymentObject $trustly): void
    {
        $this->trustly = $trustly;
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
     */
    public function setApplePay(?ApplePayPaymentObject $applePay): void
    {
        $this->applePay = $applePay;
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
     */
    public function setGooglePay(?GooglePayWalletResponse $googlePay): void
    {
        $this->googlePay = $googlePay;
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
     */
    public function setVenmo(?VenmoWalletResponse $venmo): void
    {
        $this->venmo = $venmo;
    }
}
