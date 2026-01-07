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
 * Information needed to pay using Google Pay.
 */
class GooglePayRequest
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var PhoneNumberWithCountryCode|null
     */
    private $phoneNumber;

    /**
     * @var GooglePayRequestCard|null
     */
    private $card;

    /**
     * @var GooglePayDecryptedTokenData|null
     */
    private $decryptedToken;

    /**
     * @var AssuranceDetails|null
     */
    private $assuranceDetails;

    /**
     * @var GooglePayExperienceContext|null
     */
    private $experienceContext;

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
    public function getPhoneNumber(): ?PhoneNumberWithCountryCode
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
    public function setPhoneNumber(?PhoneNumberWithCountryCode $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Returns Card.
     * The payment card used to fund a Google Pay payment. Can be a credit or debit card.
     */
    public function getCard(): ?GooglePayRequestCard
    {
        return $this->card;
    }

    /**
     * Sets Card.
     * The payment card used to fund a Google Pay payment. Can be a credit or debit card.
     *
     * @maps card
     */
    public function setCard(?GooglePayRequestCard $card): void
    {
        $this->card = $card;
    }

    /**
     * Returns Decrypted Token.
     * Details shared by Google for the merchant to be shared with PayPal. This is required to process the
     * transaction using the Google Pay payment method.
     */
    public function getDecryptedToken(): ?GooglePayDecryptedTokenData
    {
        return $this->decryptedToken;
    }

    /**
     * Sets Decrypted Token.
     * Details shared by Google for the merchant to be shared with PayPal. This is required to process the
     * transaction using the Google Pay payment method.
     *
     * @maps decrypted_token
     */
    public function setDecryptedToken(?GooglePayDecryptedTokenData $decryptedToken): void
    {
        $this->decryptedToken = $decryptedToken;
    }

    /**
     * Returns Assurance Details.
     * Information about cardholder possession validation and cardholder identification and verifications
     * (ID&V).
     */
    public function getAssuranceDetails(): ?AssuranceDetails
    {
        return $this->assuranceDetails;
    }

    /**
     * Sets Assurance Details.
     * Information about cardholder possession validation and cardholder identification and verifications
     * (ID&V).
     *
     * @maps assurance_details
     */
    public function setAssuranceDetails(?AssuranceDetails $assuranceDetails): void
    {
        $this->assuranceDetails = $assuranceDetails;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     */
    public function getExperienceContext(): ?GooglePayExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     *
     * @maps experience_context
     */
    public function setExperienceContext(?GooglePayExperienceContext $experienceContext): void
    {
        $this->experienceContext = $experienceContext;
    }
}
