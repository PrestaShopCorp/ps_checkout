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
 * Details shared by Google for the merchant to be shared with PayPal. This is required to process the
 * transaction using the Google Pay payment method.
 */
class GooglePayDecryptedTokenData
{
    /**
     * @var string|null
     */
    private $messageId;

    /**
     * @var string|null
     */
    private $messageExpiration;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var GooglePayCard
     */
    private $card;

    /**
     * @var string
     */
    private $authenticationMethod;

    /**
     * @var string|null
     */
    private $cryptogram;

    /**
     * @var string|null
     */
    private $eciIndicator;

    /**
     * @param string $paymentMethod
     * @param GooglePayCard $card
     * @param string $authenticationMethod
     */
    public function __construct(string $paymentMethod, GooglePayCard $card, string $authenticationMethod)
    {
        $this->paymentMethod = $paymentMethod;
        $this->card = $card;
        $this->authenticationMethod = $authenticationMethod;
    }

    /**
     * Returns Message Id.
     * A unique ID that identifies the message in case it needs to be revoked or located at a later time.
     */
    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    /**
     * Sets Message Id.
     * A unique ID that identifies the message in case it needs to be revoked or located at a later time.
     *
     * @maps message_id
     * @return self
     */
    public function setMessageId(?string $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    /**
     * Returns Message Expiration.
     * Date and time at which the message expires as UTC milliseconds since epoch. Integrators should
     * reject any message that's expired.
     */
    public function getMessageExpiration(): ?string
    {
        return $this->messageExpiration;
    }

    /**
     * Sets Message Expiration.
     * Date and time at which the message expires as UTC milliseconds since epoch. Integrators should
     * reject any message that's expired.
     *
     * @maps message_expiration
     * @return self
     */
    public function setMessageExpiration(?string $messageExpiration): self
    {
        $this->messageExpiration = $messageExpiration;

        return $this;
    }

    /**
     * Returns Payment Method.
     * The type of the payment credential. Currently, only CARD is supported.
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * Sets Payment Method.
     * The type of the payment credential. Currently, only CARD is supported.
     *
     * @required
     * @maps payment_method
     * @return self
     */
    public function setPaymentMethod(string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Returns Card.
     * The payment card used to fund a Google Pay payment. Can be a credit or debit card.
     */
    public function getCard(): GooglePayCard
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * The payment card used to fund a Google Pay payment. Can be a credit or debit card.
     *
     * @required
     * @maps card
     * @return self
     */
    public function setCard(GooglePayCard $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Returns Authentication Method.
     * Authentication Method which is used for the card transaction.
     */
    public function getAuthenticationMethod(): string
    {
        return $this->authenticationMethod;
    }

    /**
     * Sets Authentication Method.
     * Authentication Method which is used for the card transaction.
     *
     * @required
     * @maps authentication_method
     * @return self
     */
    public function setAuthenticationMethod(string $authenticationMethod): self
    {
        $this->authenticationMethod = $authenticationMethod;

        return $this;
    }

    /**
     * Returns Cryptogram.
     * Base-64 cryptographic identifier used by card schemes to validate the token verification result.
     * This is a conditionally required field if authentication_method is CRYPTOGRAM_3DS.
     */
    public function getCryptogram(): ?string
    {
        return $this->cryptogram;
    }

    /**
     * Sets Cryptogram.
     * Base-64 cryptographic identifier used by card schemes to validate the token verification result.
     * This is a conditionally required field if authentication_method is CRYPTOGRAM_3DS.
     *
     * @maps cryptogram
     * @return self
     */
    public function setCryptogram(?string $cryptogram): self
    {
        $this->cryptogram = $cryptogram;

        return $this;
    }

    /**
     * Returns Eci Indicator.
     * Electronic Commerce Indicator may not always be present. It is only returned for tokens on the Visa
     * card network. This value is passed through in the payment authorization request.
     */
    public function getEciIndicator(): ?string
    {
        return $this->eciIndicator;
    }

    /**
     * Sets Eci Indicator.
     * Electronic Commerce Indicator may not always be present. It is only returned for tokens on the Visa
     * card network. This value is passed through in the payment authorization request.
     *
     * @maps eci_indicator
     * @return self
     */
    public function setEciIndicator(?string $eciIndicator): self
    {
        $this->eciIndicator = $eciIndicator;

        return $this;
    }
}
