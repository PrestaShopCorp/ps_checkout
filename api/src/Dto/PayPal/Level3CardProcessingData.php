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
 * The level 3 card processing data collections, If your merchant account has been configured for Level
 * 3 processing this field will be passed to the processor on your behalf. Please contact your PayPal
 * Technical Account Manager to define level 3 data for your business.
 */
class Level3CardProcessingData
{
    /**
     * @var Money|null
     */
    private $shippingAmount;

    /**
     * @var Money|null
     */
    private $dutyAmount;

    /**
     * @var Money|null
     */
    private $discountAmount;

    /**
     * @var Address|null
     */
    private $shippingAddress;

    /**
     * @var string|null
     */
    private $shipsFromPostalCode;

    /**
     * @var LineItem[]|null
     */
    private $lineItems;

    /**
     * Returns Shipping Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getShippingAmount(): ?Money
    {
        return $this->shippingAmount;
    }

    /**
     * Sets Shipping Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps shipping_amount
     * @return self
     */
    public function setShippingAmount(?Money $shippingAmount): self
    {
        $this->shippingAmount = $shippingAmount;

        return $this;
    }

    /**
     * Returns Duty Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getDutyAmount(): ?Money
    {
        return $this->dutyAmount;
    }

    /**
     * Sets Duty Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps duty_amount
     * @return self
     */
    public function setDutyAmount(?Money $dutyAmount): self
    {
        $this->dutyAmount = $dutyAmount;

        return $this;
    }

    /**
     * Returns Discount Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getDiscountAmount(): ?Money
    {
        return $this->discountAmount;
    }

    /**
     * Sets Discount Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps discount_amount
     * @return self
     */
    public function setDiscountAmount(?Money $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    /**
     * Returns Shipping Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     */
    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    /**
     * Sets Shipping Address.
     * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
     * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
     * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
     * controls-the-autocomplete-attribute).
     *
     * @maps shipping_address
     * @return self
     */
    public function setShippingAddress(?Address $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Returns Ships From Postal Code.
     * Use this field to specify the postal code of the shipping location.
     */
    public function getShipsFromPostalCode(): ?string
    {
        return $this->shipsFromPostalCode;
    }

    /**
     * Sets Ships From Postal Code.
     * Use this field to specify the postal code of the shipping location.
     *
     * @maps ships_from_postal_code
     * @return self
     */
    public function setShipsFromPostalCode(?string $shipsFromPostalCode): self
    {
        $this->shipsFromPostalCode = $shipsFromPostalCode;

        return $this;
    }

    /**
     * Returns Line Items.
     * A list of the items that were purchased with this payment. If your merchant account has been
     * configured for Level 3 processing this field will be passed to the processor on your behalf.
     *
     * @return LineItem[]|null
     */
    public function getLineItems(): ?array
    {
        return $this->lineItems;
    }

    /**
     * Sets Line Items.
     * A list of the items that were purchased with this payment. If your merchant account has been
     * configured for Level 3 processing this field will be passed to the processor on your behalf.
     *
     * @maps line_items
     *
     * @param LineItem[]|null $lineItems
     * @return self
     */
    public function setLineItems(?array $lineItems): self
    {
        $this->lineItems = $lineItems;

        return $this;
    }
}
