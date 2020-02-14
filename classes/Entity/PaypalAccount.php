<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
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
    const PS_CHECKOUT_PAYPAL_EMAIL_STATUS = 'PS_CHECKOUT_PAYPAL_EMAIL_STATUS';
    const PS_CHECKOUT_PAYPAL_PAYMENT_STATUS = 'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS';
    const PS_CHECKOUT_CARD_PAYMENT_STATUS = 'PS_CHECKOUT_CARD_PAYMENT_STATUS';
    const PS_CHECKOUT_CARD_PAYMENT_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';

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

    public function __construct($merchantId = null, $email = null, $emailIsVerified = null, $paypalPaymentStatus = null, $cardPaymentStatus = null)
    {
        $this->setMerchantId($merchantId);
        $this->setEmail($email);
        $this->setEmailIsVerified($emailIsVerified);
        $this->setPaypalPaymentStatus($paypalPaymentStatus);
        $this->setCardPaymentStatus($cardPaymentStatus);
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
     * @return int|string $status
     */
    public function setPaypalPaymentStatus($status)
    {
        $this->paypalPaymentStatus = $status;
    }

    /**
     * Setter for cardPaymentStatus
     *
     * @return string $status
     */
    public function setCardPaymentStatus($status)
    {
        $this->cardPaymentStatus = $status;
    }
}
