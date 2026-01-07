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
 * The payment card to use to fund a payment. Can be a credit or debit card. Note: Passing card number,
 * cvv and expiry directly via the API requires PCI SAQ D compliance. *PayPal offers a mechanism by
 * which you do not have to take on the PCI SAQ D burden by using hosted fields - refer to this
 * Integration Guide*.
 */
class CardRequest
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $number;

    /**
     * @var string|null
     */
    private $expiry;

    /**
     * @var string|null
     */
    private $securityCode;

    /**
     * @var Address|null
     */
    private $billingAddress;

    /**
     * @var CardAttributes|null
     */
    private $attributes;

    /**
     * @var string|null
     */
    private $vaultId;

    /**
     * @var string|null
     */
    private $singleUseToken;

    /**
     * @var CardStoredCredential|null
     */
    private $storedCredential;

    /**
     * @var NetworkToken|null
     */
    private $networkToken;

    /**
     * @var CardExperienceContext|null
     */
    private $experienceContext;

    /**
     * Returns Name.
     * The card holder's name as it appears on the card.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The card holder's name as it appears on the card.
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
     * Returns Number.
     * The primary account number (PAN) for the payment card.
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * Sets Number.
     * The primary account number (PAN) for the payment card.
     *
     * @maps number
     * @return self
     */
    public function setNumber(?string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Returns Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     */
    public function getExpiry(): ?string
    {
        return $this->expiry;
    }

    /**
     * Sets Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @maps expiry
     * @return self
     */
    public function setExpiry(?string $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Returns Security Code.
     * The three- or four-digit security code of the card. Also known as the CVV, CVC, CVN, CVE, or CID.
     * This parameter cannot be present in the request when `payment_initiator=MERCHANT`.
     */
    public function getSecurityCode(): ?string
    {
        return $this->securityCode;
    }

    /**
     * Sets Security Code.
     * The three- or four-digit security code of the card. Also known as the CVV, CVC, CVN, CVE, or CID.
     * This parameter cannot be present in the request when `payment_initiator=MERCHANT`.
     *
     * @maps security_code
     * @return self
     */
    public function setSecurityCode(?string $securityCode): self
    {
        $this->securityCode = $securityCode;

        return $this;
    }

    /**
     * Returns Billing Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     */
    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    /**
     * Sets Billing Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     *
     * @maps billing_address
     * @return self
     */
    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of this card.
     */
    public function getAttributes(): ?CardAttributes
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of this card.
     *
     * @maps attributes
     * @return self
     */
    public function setAttributes(?CardAttributes $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
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
     * @return self
     */
    public function setVaultId(?string $vaultId): self
    {
        $this->vaultId = $vaultId;

        return $this;
    }

    /**
     * Returns Single Use Token.
     * The PayPal-generated, short-lived, one-time-use token, used to communicate payment information to
     * PayPal for transaction processing.
     */
    public function getSingleUseToken(): ?string
    {
        return $this->singleUseToken;
    }

    /**
     * Sets Single Use Token.
     * The PayPal-generated, short-lived, one-time-use token, used to communicate payment information to
     * PayPal for transaction processing.
     *
     * @maps single_use_token
     * @return self
     */
    public function setSingleUseToken(?string $singleUseToken): self
    {
        $this->singleUseToken = $singleUseToken;

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

    /**
     * Returns Network Token.
     * The Third Party Network token used to fund a payment.
     */
    public function getNetworkToken(): ?NetworkToken
    {
        return $this->networkToken;
    }

    /**
     * Sets Network Token.
     * The Third Party Network token used to fund a payment.
     *
     * @maps network_token
     * @return self
     */
    public function setNetworkToken(?NetworkToken $networkToken): self
    {
        $this->networkToken = $networkToken;

        return $this;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the 3DS Approval for payment.
     */
    public function getExperienceContext(): ?CardExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the 3DS Approval for payment.
     *
     * @maps experience_context
     * @return self
     */
    public function setExperienceContext(?CardExperienceContext $experienceContext): self
    {
        $this->experienceContext = $experienceContext;

        return $this;
    }
}
