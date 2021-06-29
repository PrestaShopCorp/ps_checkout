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

namespace PrestaShop\Module\PrestashopCheckout\Entity;

/**
 * Not really an entity.
 * Define and manage data regarding paypal account
 */
class PaypalAccount
{
    /**
     * Const list of databse fields used for store data
     */
    const PS_CHECKOUT_PAYPAL_ID_MERCHANT = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';
    const PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT = 'PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT';
    const PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT = 'PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT';
    const PS_CHECKOUT_PAYPAL_EMAIL_STATUS = 'PS_CHECKOUT_PAYPAL_EMAIL_STATUS';
    const PS_CHECKOUT_PAYPAL_PAYMENT_STATUS = 'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS';
    const PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS = 'PS_CHECKOUT_CARD_PAYMENT_STATUS';
    const PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_CARDS_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_CARDS_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_PAYPAL_CREDIT_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_PAYPAL_CREDIT_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_VENMO_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_VENMO_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_SEPA_LASTSCHRIFT_CREDIT_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_SEPA_LASTSCHRIFT_CREDIT_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_BANCONTACT_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_BANCONTACT_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_EPS_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_EPS_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_GIROPAY_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_GIROPAY_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_IDEAL_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_IDEAL_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_MYBANK_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_MYBANK_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_PRZELEWY24_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_PRZELEWY24_ENABLED';
    const PS_CHECKOUT_FUNDING_SOURCE_SOFORT_ENABLED = 'PS_CHECKOUT_FUNDING_SOURCE_SOFORT_ENABLED';
    const PS_CHECKOUT_CARD_VISA_ENABLED = 'PS_CHECKOUT_CARD_VISA_ENABLED';
    const PS_CHECKOUT_MASTER_CARD_ENABLED = 'PS_CHECKOUT_MASTER_CARD_ENABLED';
    const PS_CHECKOUT_CARD_AMEX_ENABLED = 'PS_CHECKOUT_CARD_AMEX_ENABLED';
    const PS_CHECKOUT_CARD_DISCOVER_ENABLED = 'PS_CHECKOUT_CARD_DISCOVER_ENABLED';
    const PS_CHECKOUT_CARD_JCB_ENABLED = 'PS_CHECKOUT_CARD_JCB_ENABLED';
    const PS_CHECKOUT_CARD_ELO_ENABLED = 'PS_CHECKOUT_CARD_ELO_ENABLED';
    const PS_CHECKOUT_CARD_HIPER_ENABLED = 'PS_CHECKOUT_CARD_HIPER_ENABLED';

    /**
     * @var string
     */
    private $merchantId;

    /**
     * Email of the merchant
     *
     * @var string
     */
    private $email;

    /**
     * Status of the email, if it has been validated or not
     *
     * @var int
     */
    private $emailIsVerified;

    /**
     * Paypal payment method status
     *
     * @var int
     */
    private $paypalPaymentStatus;

    /**
     * Card payment method status
     *
     * @var string
     */
    private $cardPaymentStatus;

    /**
     * Merchant country ISO code
     *
     * @var string
     */
    private $merchantCountry;

    public function __construct($merchantId = null, $email = null, $emailIsVerified = null, $paypalPaymentStatus = null, $cardPaymentStatus = null, $merchantCountry = null)
    {
        $this->setMerchantId($merchantId);
        $this->setEmail($email);
        $this->setEmailIsVerified($emailIsVerified);
        $this->setPaypalPaymentStatus($paypalPaymentStatus);
        $this->setCardPaymentStatus($cardPaymentStatus);
        $this->setMerchantCountry($merchantCountry);
    }

    /**
     * Getter for merchantId
     *
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * Getter for email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Getter for merchantCountry
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        return $this->merchantCountry;
    }

    /**
     * Getter for emailVerified
     *
     * @return int
     */
    public function getEmailIsVerified()
    {
        return $this->emailIsVerified;
    }

    /**
     * Getter for paypalPaymentStatus
     *
     * @return int
     */
    public function getPaypalPaymentStatus()
    {
        return $this->paypalPaymentStatus;
    }

    /**
     * Getter for cardPaymentStatus
     */
    public function getCardPaymentStatus()
    {
        return $this->cardPaymentStatus;
    }

    /**
     * Setter for merchantId
     *
     * @param string $merchantId
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * Setter for email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Setter for merchantCountry
     *
     * @param string $merchantCountry
     */
    public function setMerchantCountry($merchantCountry)
    {
        $this->merchantCountry = $merchantCountry;
    }

    /**
     * Setter for emailVerified
     *
     * @param int|string $status
     */
    public function setEmailIsVerified($status)
    {
        $this->emailIsVerified = $status;
    }

    /**
     * Setter for paypalPaymentStatus
     *
     * @param int|string $status
     */
    public function setPaypalPaymentStatus($status)
    {
        $this->paypalPaymentStatus = $status;
    }

    /**
     * Setter for cardPaymentStatus
     *
     * @param string $status
     */
    public function setCardPaymentStatus($status)
    {
        $this->cardPaymentStatus = $status;
    }
}
