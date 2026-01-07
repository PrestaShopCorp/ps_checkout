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
 * A resource that identifies a PayPal Wallet is used for payment.
 */
class PaypalWallet
{
    /**
     * @var string|null
     */
    private $vaultId;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var Name|null
     */
    private $name;

    /**
     * @var PhoneWithType|null
     */
    private $phone;

    /**
     * @var string|null
     */
    private $birthDate;

    /**
     * @var TaxInfo|null
     */
    private $taxInfo;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var PaypalWalletAttributes|null
     */
    private $attributes;

    /**
     * @var PaypalWalletExperienceContext|null
     */
    private $experienceContext;

    /**
     * @var string|null
     */
    private $billingAgreementId;

    /**
     * @var PaypalWalletStoredCredential|null
     */
    private $storedCredential;

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
     * Returns Name.
     * The name of the party.
     */
    public function getName(): ?Name
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The name of the party.
     *
     * @maps name
     */
    public function setName(?Name $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns Phone.
     * The phone information.
     */
    public function getPhone(): ?PhoneWithType
    {
        return $this->phone;
    }

    /**
     * Sets Phone.
     * The phone information.
     *
     * @maps phone
     */
    public function setPhone(?PhoneWithType $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Returns Birth Date.
     * The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-
     * 5.6). To represent special legal values, such as a date of birth, you should use dates with no
     * associated time or time-zone data. Whenever possible, use the standard `date_time` type. This
     * regular expression does not validate all dates. For example, February 31 is valid and nothing is
     * known about leap years.
     */
    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    /**
     * Sets Birth Date.
     * The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-
     * 5.6). To represent special legal values, such as a date of birth, you should use dates with no
     * associated time or time-zone data. Whenever possible, use the standard `date_time` type. This
     * regular expression does not validate all dates. For example, February 31 is valid and nothing is
     * known about leap years.
     *
     * @maps birth_date
     */
    public function setBirthDate(?string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * Returns Tax Info.
     * The tax ID of the customer. The customer is also known as the payer. Both `tax_id` and `tax_id_type`
     * are required.
     */
    public function getTaxInfo(): ?TaxInfo
    {
        return $this->taxInfo;
    }

    /**
     * Sets Tax Info.
     * The tax ID of the customer. The customer is also known as the payer. Both `tax_id` and `tax_id_type`
     * are required.
     *
     * @maps tax_info
     */
    public function setTaxInfo(?TaxInfo $taxInfo): void
    {
        $this->taxInfo = $taxInfo;
    }

    /**
     * Returns Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     */
    public function getAddress(): ?Address
    {
        return $this->address;
    }

    /**
     * Sets Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     *
     * @maps address
     */
    public function setAddress(?Address $address): void
    {
        $this->address = $address;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of this PayPal Wallet.
     */
    public function getAttributes(): ?PaypalWalletAttributes
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of this PayPal Wallet.
     *
     * @maps attributes
     */
    public function setAttributes(?PaypalWalletAttributes $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the approval process for payment with PayPal. Note: Partners
     * and Marketplaces might configure brand_name and shipping_preference during partner account setup,
     * which overrides the request values.
     */
    public function getExperienceContext(): ?PaypalWalletExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the approval process for payment with PayPal. Note: Partners
     * and Marketplaces might configure brand_name and shipping_preference during partner account setup,
     * which overrides the request values.
     *
     * @maps experience_context
     */
    public function setExperienceContext(?PaypalWalletExperienceContext $experienceContext): void
    {
        $this->experienceContext = $experienceContext;
    }

    /**
     * Returns Billing Agreement Id.
     * The PayPal billing agreement ID. References an approved recurring payment for goods or services.
     */
    public function getBillingAgreementId(): ?string
    {
        return $this->billingAgreementId;
    }

    /**
     * Sets Billing Agreement Id.
     * The PayPal billing agreement ID. References an approved recurring payment for goods or services.
     *
     * @maps billing_agreement_id
     */
    public function setBillingAgreementId(?string $billingAgreementId): void
    {
        $this->billingAgreementId = $billingAgreementId;
    }

    /**
     * Returns Stored Credential.
     * Provides additional details to process a payment using the PayPal wallet billing agreement or a
     * vaulted payment method that has been stored or is intended to be stored.
     */
    public function getStoredCredential(): ?PaypalWalletStoredCredential
    {
        return $this->storedCredential;
    }

    /**
     * Sets Stored Credential.
     * Provides additional details to process a payment using the PayPal wallet billing agreement or a
     * vaulted payment method that has been stored or is intended to be stored.
     *
     * @maps stored_credential
     */
    public function setStoredCredential(?PaypalWalletStoredCredential $storedCredential): void
    {
        $this->storedCredential = $storedCredential;
    }
}
