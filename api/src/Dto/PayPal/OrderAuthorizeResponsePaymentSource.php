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
class OrderAuthorizeResponsePaymentSource
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
