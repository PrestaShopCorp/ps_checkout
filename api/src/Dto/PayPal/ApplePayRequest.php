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
class ApplePayRequest
{
    /**
     * @var string|null
     */
    private $id;

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
     * @var ApplePayDecryptedTokenData|null
     */
    private $decryptedToken;

    /**
     * @var CardStoredCredential|null
     */
    private $storedCredential;

    /**
     * @var string|null
     */
    private $vaultId;

    /**
     * @var ApplePayAttributes|null
     */
    private $attributes;

    /**
     * @var ApplePayExperienceContext|null
     */
    private $experienceContext;

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
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
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
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
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
     */
    public function setEmailAddress(?string $emailAddress): void
    {
        $this->emailAddress = $emailAddress;
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
     */
    public function setPhoneNumber(?PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Returns Decrypted Token.
     * Information about the Payment data obtained by decrypting Apple Pay token.
     */
    public function getDecryptedToken(): ?ApplePayDecryptedTokenData
    {
        return $this->decryptedToken;
    }

    /**
     * Sets Decrypted Token.
     * Information about the Payment data obtained by decrypting Apple Pay token.
     *
     * @maps decrypted_token
     */
    public function setDecryptedToken(?ApplePayDecryptedTokenData $decryptedToken): void
    {
        $this->decryptedToken = $decryptedToken;
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
     */
    public function setStoredCredential(?CardStoredCredential $storedCredential): void
    {
        $this->storedCredential = $storedCredential;
    }

    /**
     * Returns Vault Id.
     * The PayPal-generated ID for the vaulted payment source. This ID should be stored on the merchant's
     * server so the saved payment source can be used for future transactions.
     */
    public function getVaultId(): ?string
    {
        return $this->vaultId;
    }

    /**
     * Sets Vault Id.
     * The PayPal-generated ID for the vaulted payment source. This ID should be stored on the merchant's
     * server so the saved payment source can be used for future transactions.
     *
     * @maps vault_id
     */
    public function setVaultId(?string $vaultId): void
    {
        $this->vaultId = $vaultId;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with apple pay.
     */
    public function getAttributes(): ?ApplePayAttributes
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with apple pay.
     *
     * @maps attributes
     */
    public function setAttributes(?ApplePayAttributes $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     */
    public function getExperienceContext(): ?ApplePayExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     *
     * @maps experience_context
     */
    public function setExperienceContext(?ApplePayExperienceContext $experienceContext): void
    {
        $this->experienceContext = $experienceContext;
    }
}
