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

/**
 * The payment card to use to fund a Google Pay payment response. Can be a credit or debit card.
 */
class GooglePayCardResponse
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
    private $type;

    /**
     * @var string|null
     */
    private $brand;

    /**
     * @var Address|null
     */
    private $billingAddress;

    /**
     * @var AuthenticationResponse|null
     */
    private $authenticationResult;

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
     */
    public function setBillingAddress(?Address $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
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
}
