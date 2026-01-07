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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\CardStoredCredential;

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
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
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
     */
    public function setLastDigits(?string $lastDigits): void
    {
        $this->lastDigits = $lastDigits;
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
     */
    public function setBrand(?string $brand): void
    {
        $this->brand = $brand;
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
     */
    public function setAvailableNetworks(?array $availableNetworks): void
    {
        $this->availableNetworks = $availableNetworks;
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
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
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
     */
    public function setAuthenticationResult(?AuthenticationResponse $authenticationResult): void
    {
        $this->authenticationResult = $authenticationResult;
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
     */
    public function setAttributes(?CardAttributesResponse $attributes): void
    {
        $this->attributes = $attributes;
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
     */
    public function setFromRequest(?CardFromRequest $fromRequest): void
    {
        $this->fromRequest = $fromRequest;
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
     */
    public function setExpiry(?string $expiry): void
    {
        $this->expiry = $expiry;
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
     */
    public function setBinDetails(?BinDetails $binDetails): void
    {
        $this->binDetails = $binDetails;
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
}
