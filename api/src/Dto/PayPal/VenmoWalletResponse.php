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
 * Venmo wallet response.
 */
class VenmoWalletResponse
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
    private $userName;

    /**
     * @var Name|null
     */
    private $name;

    /**
     * @var PhoneNumber|null
     */
    private $phoneNumber;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * @var string|null
     */
    private $returnFlow = ReturnFlow::AUTO;

    /**
     * @var VenmoWalletAttributesResponse|null
     */
    private $attributes;

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
     * Returns User Name.
     * The Venmo user name chosen by the user, also know as a Venmo handle.
     */
    public function getUserName(): ?string
    {
        return $this->userName;
    }

    /**
     * Sets User Name.
     * The Venmo user name chosen by the user, also know as a Venmo handle.
     *
     * @maps user_name
     * @return self
     */
    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

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
     * Returns Return Flow.
     * Merchant preference on how the buyer can navigate back to merchant website post approving the
     * transaction on the Venmo App.
     */
    public function getReturnFlow(): ?string
    {
        return $this->returnFlow;
    }

    /**
     * Sets Return Flow.
     * Merchant preference on how the buyer can navigate back to merchant website post approving the
     * transaction on the Venmo App.
     *
     * @maps return_flow
     * @return self
     */
    public function setReturnFlow(?string $returnFlow): self
    {
        $this->returnFlow = $returnFlow;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of a Venmo Wallet.
     */
    public function getAttributes(): ?VenmoWalletAttributesResponse
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of a Venmo Wallet.
     *
     * @maps attributes
     * @return self
     */
    public function setAttributes(?VenmoWalletAttributesResponse $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
