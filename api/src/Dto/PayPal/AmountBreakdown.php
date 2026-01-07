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
 * The breakdown of the amount. Breakdown provides details such as total item amount, total tax amount,
 * shipping, handling, insurance, and discounts, if any.
 */
class AmountBreakdown
{
    /**
     * @var Money|null
     */
    private $itemTotal;

    /**
     * @var Money|null
     */
    private $shipping;

    /**
     * @var Money|null
     */
    private $handling;

    /**
     * @var Money|null
     */
    private $taxTotal;

    /**
     * @var Money|null
     */
    private $insurance;

    /**
     * @var Money|null
     */
    private $shippingDiscount;

    /**
     * @var Money|null
     */
    private $discount;

    /**
     * Returns Item Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getItemTotal(): ?Money
    {
        return $this->itemTotal;
    }

    /**
     * Sets Item Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps item_total
     */
    public function setItemTotal(?Money $itemTotal): void
    {
        $this->itemTotal = $itemTotal;
    }

    /**
     * Returns Shipping.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getShipping(): ?Money
    {
        return $this->shipping;
    }

    /**
     * Sets Shipping.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps shipping
     */
    public function setShipping(?Money $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * Returns Handling.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getHandling(): ?Money
    {
        return $this->handling;
    }

    /**
     * Sets Handling.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps handling
     */
    public function setHandling(?Money $handling): void
    {
        $this->handling = $handling;
    }

    /**
     * Returns Tax Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getTaxTotal(): ?Money
    {
        return $this->taxTotal;
    }

    /**
     * Sets Tax Total.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps tax_total
     */
    public function setTaxTotal(?Money $taxTotal): void
    {
        $this->taxTotal = $taxTotal;
    }

    /**
     * Returns Insurance.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getInsurance(): ?Money
    {
        return $this->insurance;
    }

    /**
     * Sets Insurance.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps insurance
     */
    public function setInsurance(?Money $insurance): void
    {
        $this->insurance = $insurance;
    }

    /**
     * Returns Shipping Discount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getShippingDiscount(): ?Money
    {
        return $this->shippingDiscount;
    }

    /**
     * Sets Shipping Discount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps shipping_discount
     */
    public function setShippingDiscount(?Money $shippingDiscount): void
    {
        $this->shippingDiscount = $shippingDiscount;
    }

    /**
     * Returns Discount.
     * The discount amount and currency code. For list of supported currencies and decimal precision, see
     * the PayPal REST APIs Currency Codes.
     */
    public function getDiscount(): ?Money
    {
        return $this->discount;
    }

    /**
     * Sets Discount.
     * The discount amount and currency code. For list of supported currencies and decimal precision, see
     * the PayPal REST APIs Currency Codes.
     *
     * @maps discount
     */
    public function setDiscount(?Money $discount): void
    {
        $this->discount = $discount;
    }
}
