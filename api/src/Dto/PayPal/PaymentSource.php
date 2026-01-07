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
 * The payment source definition.
 */
class PaymentSource
{
    /**
     * @var CardRequest|null
     */
    private $card;

    /**
     * @var Token|null
     */
    private $token;

    /**
     * @var PaypalWallet|null
     */
    private $paypal;

    /**
     * @var BancontactPaymentRequest|null
     */
    private $bancontact;

    /**
     * @var BlikPaymentRequest|null
     */
    private $blik;

    /**
     * @var EpsPaymentRequest|null
     */
    private $eps;

    /**
     * @var GiropayPaymentRequest|null
     */
    private $giropay;

    /**
     * @var IdealPaymentRequest|null
     */
    private $ideal;

    /**
     * @var MybankPaymentRequest|null
     */
    private $mybank;

    /**
     * @var P24PaymentRequest|null
     */
    private $p24;

    /**
     * @var SofortPaymentRequest|null
     */
    private $sofort;

    /**
     * @var TrustlyPaymentRequest|null
     */
    private $trustly;

    /**
     * @var ApplePayRequest|null
     */
    private $applePay;

    /**
     * @var GooglePayRequest|null
     */
    private $googlePay;

    /**
     * @var VenmoWalletRequest|null
     */
    private $venmo;

    /**
     * Returns Card.
     * The payment card to use to fund a payment. Can be a credit or debit card. Note: Passing card number,
     * cvv and expiry directly via the API requires PCI SAQ D compliance. *PayPal offers a mechanism by
     * which you do not have to take on the PCI SAQ D burden by using hosted fields - refer to this
     * Integration Guide*.
     */
    public function getCard(): ?CardRequest
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * The payment card to use to fund a payment. Can be a credit or debit card. Note: Passing card number,
     * cvv and expiry directly via the API requires PCI SAQ D compliance. *PayPal offers a mechanism by
     * which you do not have to take on the PCI SAQ D burden by using hosted fields - refer to this
     * Integration Guide*.
     *
     * @maps card
     *
     * @return self
     */
    public function setCard(?CardRequest $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Returns Token.
     * The tokenized payment source to fund a payment.
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }

    /**
     * Sets Token.
     * The tokenized payment source to fund a payment.
     *
     * @maps token
     * @return self
     */
    public function setToken(?Token $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Returns Paypal.
     * A resource that identifies a PayPal Wallet is used for payment.
     */
    public function getPaypal(): ?PaypalWallet
    {
        return $this->paypal;
    }

    /**
     * Sets Paypal.
     * A resource that identifies a PayPal Wallet is used for payment.
     *
     * @maps paypal
     * @return self
     */
    public function setPaypal(?PaypalWallet $paypal): self
    {
        $this->paypal = $paypal;

        return $this;
    }

    /**
     * Returns Bancontact.
     * Information needed to pay using Bancontact.
     */
    public function getBancontact(): ?BancontactPaymentRequest
    {
        return $this->bancontact;
    }

    /**
     * Sets Bancontact.
     * Information needed to pay using Bancontact.
     *
     * @maps bancontact
     * @return self
     */
    public function setBancontact(?BancontactPaymentRequest $bancontact): self
    {
        $this->bancontact = $bancontact;

        return $this;
    }

    /**
     * Returns Blik.
     * Information needed to pay using BLIK.
     */
    public function getBlik(): ?BlikPaymentRequest
    {
        return $this->blik;
    }

    /**
     * Sets Blik.
     * Information needed to pay using BLIK.
     *
     * @maps blik
     * @return self
     */
    public function setBlik(?BlikPaymentRequest $blik): self
    {
        $this->blik = $blik;

        return $this;
    }

    /**
     * Returns Eps.
     * Information needed to pay using eps.
     */
    public function getEps(): ?EpsPaymentRequest
    {
        return $this->eps;
    }

    /**
     * Sets Eps.
     * Information needed to pay using eps.
     *
     * @maps eps
     * @return self
     */
    public function setEps(?EpsPaymentRequest $eps): self
    {
        $this->eps = $eps;

        return $this;
    }

    /**
     * Returns Giropay.
     * Information needed to pay using giropay.
     */
    public function getGiropay(): ?GiropayPaymentRequest
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
    public function setGiropay(?GiropayPaymentRequest $giropay): self
    {
        $this->giropay = $giropay;

        return $this;
    }

    /**
     * Returns Ideal.
     * Information needed to pay using iDEAL.
     */
    public function getIdeal(): ?IdealPaymentRequest
    {
        return $this->ideal;
    }

    /**
     * Sets Ideal.
     * Information needed to pay using iDEAL.
     *
     * @maps ideal
     * @return self
     */
    public function setIdeal(?IdealPaymentRequest $ideal): self
    {
        $this->ideal = $ideal;

        return $this;
    }

    /**
     * Returns Mybank.
     * Information needed to pay using MyBank.
     */
    public function getMybank(): ?MybankPaymentRequest
    {
        return $this->mybank;
    }

    /**
     * Sets Mybank.
     * Information needed to pay using MyBank.
     *
     * @maps mybank
     * @return self
     */
    public function setMybank(?MybankPaymentRequest $mybank): self
    {
        $this->mybank = $mybank;

        return $this;
    }

    /**
     * Returns P 24.
     * Information needed to pay using P24 (Przelewy24).
     */
    public function getP24(): ?P24PaymentRequest
    {
        return $this->p24;
    }

    /**
     * Sets P 24.
     * Information needed to pay using P24 (Przelewy24).
     *
     * @maps p24
     * @return self
     */
    public function setP24(?P24PaymentRequest $p24): self
    {
        $this->p24 = $p24;

        return $this;
    }

    /**
     * Returns Sofort.
     * Information needed to pay using Sofort.
     */
    public function getSofort(): ?SofortPaymentRequest
    {
        return $this->sofort;
    }

    /**
     * Sets Sofort.
     * Information needed to pay using Sofort.
     *
     * @maps sofort
     * @return self
     */
    public function setSofort(?SofortPaymentRequest $sofort): self
    {
        $this->sofort = $sofort;

        return $this;
    }

    /**
     * Returns Trustly.
     * Information needed to pay using Trustly.
     */
    public function getTrustly(): ?TrustlyPaymentRequest
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
    public function setTrustly(?TrustlyPaymentRequest $trustly): self
    {
        $this->trustly = $trustly;

        return $this;
    }

    /**
     * Returns Apple Pay.
     * Information needed to pay using ApplePay.
     */
    public function getApplePay(): ?ApplePayRequest
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
    public function setApplePay(?ApplePayRequest $applePay): self
    {
        $this->applePay = $applePay;

        return $this;
    }

    /**
     * Returns Google Pay.
     * Information needed to pay using Google Pay.
     */
    public function getGooglePay(): ?GooglePayRequest
    {
        return $this->googlePay;
    }

    /**
     * Sets Google Pay.
     * Information needed to pay using Google Pay.
     *
     * @maps google_pay
     * @return self
     */
    public function setGooglePay(?GooglePayRequest $googlePay): self
    {
        $this->googlePay = $googlePay;

        return $this;
    }

    /**
     * Returns Venmo.
     * Information needed to pay using Venmo.
     */
    public function getVenmo(): ?VenmoWalletRequest
    {
        return $this->venmo;
    }

    /**
     * Sets Venmo.
     * Information needed to pay using Venmo.
     *
     * @maps venmo
     * @return self
     */
    public function setVenmo(?VenmoWalletRequest $venmo): self
    {
        $this->venmo = $venmo;

        return $this;
    }
}
