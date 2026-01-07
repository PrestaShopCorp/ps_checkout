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
class OrderCaptureRequestPaymentSource
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
