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
 * Information needed to pay using ApplePay.
 */
class ApplePayPaymentObject
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $token;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var PhoneNumber|null
     */
    private $phoneNumber;

    /**
     * @var ApplePayCardResponse|null
     */
    private $card;

    /**
     * @var ApplePayAttributesResponse|null
     */
    private $attributes;

    /**
     * @var CardStoredCredential|null
     */
    private $storedCredential;

    /**
     * Returns Id.
     * ApplePay transaction identifier, this will be the unique identifier for this transaction provided by
     * Apple. The pattern is defined by an external party and supports Unicode.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * ApplePay transaction identifier, this will be the unique identifier for this transaction provided by
     * Apple. The pattern is defined by an external party and supports Unicode.
     *
     * @maps id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns Token.
     * Encrypted ApplePay token, containing card information. This token would be base64encoded. The
     * pattern is defined by an external party and supports Unicode.
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Sets Token.
     * Encrypted ApplePay token, containing card information. This token would be base64encoded. The
     * pattern is defined by an external party and supports Unicode.
     *
     * @maps token
     * @return self
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Returns Name.
     * The full name representation like Mr J Smith.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The full name representation like Mr J Smith.
     *
     * @maps name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns Email Address.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * Sets Email Address.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     *
     * @maps email_address
     * @return self
     */
    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Returns Phone Number.
     * The phone number in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en).
     */
    public function getPhoneNumber(): ?PhoneNumber
    {
        return $this->phoneNumber;
    }

    /**
     * Sets Phone Number.
     * The phone number in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en).
     *
     * @maps phone_number
     * @return self
     */
    public function setPhoneNumber(?PhoneNumber $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Returns Card.
     * The Card from Apple Pay Wallet used to fund the payment.
     */
    public function getCard(): ?ApplePayCardResponse
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * The Card from Apple Pay Wallet used to fund the payment.
     *
     * @maps card
     * @return self
     */
    public function setCard(?ApplePayCardResponse $card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of Apple Pay.
     */
    public function getAttributes(): ?ApplePayAttributesResponse
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of Apple Pay.
     *
     * @maps attributes
     * @return self
     */
    public function setAttributes(?ApplePayAttributesResponse $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Returns Stored Credential.
     * Provides additional details to process a payment using a `card` that has been stored or is intended
     * to be stored (also referred to as stored_credential or card-on-file). Parameter compatibility:
     * `payment_type=ONE_TIME` is compatible only with `payment_initiator=CUSTOMER`. `usage=FIRST` is
     * compatible only with `payment_initiator=CUSTOMER`. `previous_transaction_reference` or
     * `previous_network_transaction_reference` is compatible only with `payment_initiator=MERCHANT`. Only
     * one of the parameters - `previous_transaction_reference` and
     * `previous_network_transaction_reference` - can be present in the request.
     */
    public function getStoredCredential(): ?CardStoredCredential
    {
        return $this->storedCredential;
    }

    /**
     * Sets Stored Credential.
     * Provides additional details to process a payment using a `card` that has been stored or is intended
     * to be stored (also referred to as stored_credential or card-on-file). Parameter compatibility:
     * `payment_type=ONE_TIME` is compatible only with `payment_initiator=CUSTOMER`. `usage=FIRST` is
     * compatible only with `payment_initiator=CUSTOMER`. `previous_transaction_reference` or
     * `previous_network_transaction_reference` is compatible only with `payment_initiator=MERCHANT`. Only
     * one of the parameters - `previous_transaction_reference` and
     * `previous_network_transaction_reference` - can be present in the request.
     *
     * @maps stored_credential
     * @return self
     */
    public function setStoredCredential(?CardStoredCredential $storedCredential): self
    {
        $this->storedCredential = $storedCredential;

        return $this;
    }
}
