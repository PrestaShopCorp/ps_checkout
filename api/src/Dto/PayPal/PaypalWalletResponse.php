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
 * The PayPal Wallet response.
 */
class PaypalWalletResponse
{
    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var string|null
     */
    private $accountId;

    /**
     * @var string|null
     */
    private $accountStatus;

    /**
     * @var Name|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $phoneType;

    /**
     * @var PhoneNumber|null
     */
    private $phoneNumber;

    /**
     * @var string|null
     */
    private $birthDate;

    /**
     * @var string|null
     */
    private $businessName;

    /**
     * @var TaxInfo|null
     */
    private $taxInfo;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var PaypalWalletAttributesResponse|null
     */
    private $attributes;

    /**
     * @var PaypalWalletStoredCredential|null
     */
    private $storedCredential;

    /**
     * @var string|null
     */
    private $experienceStatus;

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
     * Returns Account Id.
     * The PayPal payer ID, which is a masked version of the PayPal account number intended for use with
     * third parties. The account number is reversibly encrypted and a proprietary variant of Base32 is
     * used to encode the result.
     */
    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    /**
     * Sets Account Id.
     * The PayPal payer ID, which is a masked version of the PayPal account number intended for use with
     * third parties. The account number is reversibly encrypted and a proprietary variant of Base32 is
     * used to encode the result.
     *
     * @maps account_id
     * @return self
     */
    public function setAccountId(?string $accountId): self
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * Returns Account Status.
     * The account status indicates whether the buyer has verified the financial details associated with
     * their PayPal account.
     */
    public function getAccountStatus(): ?string
    {
        return $this->accountStatus;
    }

    /**
     * Sets Account Status.
     * The account status indicates whether the buyer has verified the financial details associated with
     * their PayPal account.
     *
     * @maps account_status
     * @return self
     */
    public function setAccountStatus(?string $accountStatus): self
    {
        $this->accountStatus = $accountStatus;

        return $this;
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
     * @return self
     */
    public function setName(?Name $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns Phone Type.
     * The phone type.
     */
    public function getPhoneType(): ?string
    {
        return $this->phoneType;
    }

    /**
     * Sets Phone Type.
     * The phone type.
     *
     * @maps phone_type
     * @return self
     */
    public function setPhoneType(?string $phoneType): self
    {
        $this->phoneType = $phoneType;

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
     * @return self
     */
    public function setBirthDate(?string $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /**
     * Returns Business Name.
     * The business name of the PayPal account holder (populated for business accounts only)
     */
    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    /**
     * Sets Business Name.
     * The business name of the PayPal account holder (populated for business accounts only)
     *
     * @maps business_name
     * @return self
     */
    public function setBusinessName(?string $businessName): self
    {
        $this->businessName = $businessName;

        return $this;
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
     * @return self
     */
    public function setTaxInfo(?TaxInfo $taxInfo): self
    {
        $this->taxInfo = $taxInfo;

        return $this;
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
     * @return self
     */
    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of a PayPal Wallet.
     */
    public function getAttributes(): ?PaypalWalletAttributesResponse
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of a PayPal Wallet.
     *
     * @maps attributes
     * @return self
     */
    public function setAttributes(?PaypalWalletAttributesResponse $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
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
     * @return self
     */
    public function setStoredCredential(?PaypalWalletStoredCredential $storedCredential): self
    {
        $this->storedCredential = $storedCredential;

        return $this;
    }

    /**
     * Returns Experience Status.
     * This field indicates the status of PayPal's Checkout experience throughout the order lifecycle. The
     * values reflect the current stage of the checkout process.
     */
    public function getExperienceStatus(): ?string
    {
        return $this->experienceStatus;
    }

    /**
     * Sets Experience Status.
     * This field indicates the status of PayPal's Checkout experience throughout the order lifecycle. The
     * values reflect the current stage of the checkout process.
     *
     * @maps experience_status
     * @return self
     */
    public function setExperienceStatus(?string $experienceStatus): self
    {
        $this->experienceStatus = $experienceStatus;

        return $this;
    }
}
