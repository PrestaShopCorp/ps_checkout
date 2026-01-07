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

use PsCheckout\Api\Dto\PayPal\Address;
use PsCheckout\Api\Dto\PayPal\ShippingName;
use PsCheckout\Api\Dto\PayPal\ShippingOption;

class ShippingWithTrackingDetails
{
    /**
     * @var OrderTrackerResponse[]|null
     */
    private $trackers;

    /**
     * @var ShippingName|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var PhoneNumberWithOptionalCountryCode|null
     */
    private $phoneNumber;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var ShippingOption[]|null
     */
    private $options;

    /**
     * @var Address|null
     */
    private $address;

    /**
     * Returns Trackers.
     * An array of trackers for a transaction.
     *
     * @return OrderTrackerResponse[]|null
     */
    public function getTrackers(): ?array
    {
        return $this->trackers;
    }

    /**
     * Sets Trackers.
     * An array of trackers for a transaction.
     *
     * @maps trackers
     *
     * @param OrderTrackerResponse[]|null $trackers
     */
    public function setTrackers(?array $trackers): void
    {
        $this->trackers = $trackers;
    }

    /**
     * Returns Name.
     * The name of the party.
     */
    public function getName(): ?ShippingName
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The name of the party.
     *
     * @maps name
     */
    public function setName(?ShippingName $name): void
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
    public function getPhoneNumber(): ?PhoneNumberWithOptionalCountryCode
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
    public function setPhoneNumber(?PhoneNumberWithOptionalCountryCode $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Returns Type.
     * A classification for the method of purchase fulfillment (e.g shipping, in-store pickup, etc). Either
     * `type` or `options` may be present, but not both.
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Sets Type.
     * A classification for the method of purchase fulfillment (e.g shipping, in-store pickup, etc). Either
     * `type` or `options` may be present, but not both.
     *
     * @maps type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns Options.
     * An array of shipping options that the payee or merchant offers to the payer to ship or pick up their
     * items.
     *
     * @return ShippingOption[]|null
     */
    public function getOptions(): ?array
    {
        return $this->options;
    }

    /**
     * Sets Options.
     * An array of shipping options that the payee or merchant offers to the payer to ship or pick up their
     * items.
     *
     * @maps options
     *
     * @param ShippingOption[]|null $options
     */
    public function setOptions(?array $options): void
    {
        $this->options = $options;
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
}
