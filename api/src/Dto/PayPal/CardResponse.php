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
 * The payment card to use to fund a payment. Card can be a credit or debit card.
 */
class CardResponse
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $lastDigits;

    /**
     * @var string|null
     */
    private $brand;

    /**
     * @var string[]|null
     */
    private $availableNetworks;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var AuthenticationResponse|null
     */
    private $authenticationResult;

    /**
     * @var CardAttributesResponse|null
     */
    private $attributes;

    /**
     * @var CardFromRequest|null
     */
    private $fromRequest;

    /**
     * @var string|null
     */
    private $expiry;

    /**
     * @var BinDetails|null
     */
    private $binDetails;

    /**
     * @var CardStoredCredential|null
     */
    private $storedCredential;

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
     * Returns Last Digits.
     * The last digits of the payment card.
     */
    public function getLastDigits(): ?string
    {
        return $this->lastDigits;
    }

    /**
     * Sets Last Digits.
     * The last digits of the payment card.
     *
     * @maps last_digits
     * @return self
     */
    public function setLastDigits(?string $lastDigits): self
    {
        $this->lastDigits = $lastDigits;

        return $this;
    }

    /**
     * Returns Brand.
     * The card network or brand. Applies to credit, debit, gift, and payment cards.
     */
    public function getBrand(): ?string
    {
        return $this->brand;
    }

    /**
     * Sets Brand.
     * The card network or brand. Applies to credit, debit, gift, and payment cards.
     *
     * @maps brand
     * @return self
     */
    public function setBrand(?string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Returns Available Networks.
     * Array of brands or networks associated with the card.
     *
     * @return string[]|null
     */
    public function getAvailableNetworks(): ?array
    {
        return $this->availableNetworks;
    }

    /**
     * Sets Available Networks.
     * Array of brands or networks associated with the card.
     *
     * @maps available_networks
     *
     * @param string[]|null $availableNetworks
     * @return self
     */
    public function setAvailableNetworks(?array $availableNetworks): self
    {
        $this->availableNetworks = $availableNetworks;

        return $this;
    }

    /**
     * Returns Type.
     * Type of card. i.e Credit, Debit and so on.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * Type of card. i.e Credit, Debit and so on.
     *
     * @maps type
     * @return self
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Returns Authentication Result.
     * Results of Authentication such as 3D Secure.
     */
    public function getAuthenticationResult(): ?AuthenticationResponse
    {
        return $this->authenticationResult;
    }

    /**
     * Sets Authentication Result.
     * Results of Authentication such as 3D Secure.
     *
     * @maps authentication_result
     * @return self
     */
    public function setAuthenticationResult(?AuthenticationResponse $authenticationResult): self
    {
        $this->authenticationResult = $authenticationResult;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of this card.
     */
    public function getAttributes(): ?CardAttributesResponse
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
    public function setAttributes(?CardAttributesResponse $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Returns From Request.
     * Representation of card details as received in the request.
     */
    public function getFromRequest(): ?CardFromRequest
    {
        return $this->fromRequest;
    }

    /**
     * Sets From Request.
     * Representation of card details as received in the request.
     *
     * @maps from_request
     * @return self
     */
    public function setFromRequest(?CardFromRequest $fromRequest): self
    {
        $this->fromRequest = $fromRequest;

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
     * Returns Bin Details.
     * Bank Identification Number (BIN) details used to fund a payment.
     */
    public function getBinDetails(): ?BinDetails
    {
        return $this->binDetails;
    }

    /**
     * Sets Bin Details.
     * Bank Identification Number (BIN) details used to fund a payment.
     *
     * @maps bin_details
     * @return self
     */
    public function setBinDetails(?BinDetails $binDetails): self
    {
        $this->binDetails = $binDetails;

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
