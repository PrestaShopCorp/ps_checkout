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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class AmountBreakdown
{
    /**
     * @var Amount
     */
    private $item_total;
    /**
     * @var Amount
     */
    private $shipping;
    /**
     * @var Amount
     */
    private $handling;
    /**
     * @var Amount
     */
    private $tax_total;
    /**
     * @var Amount
     */
    private $insurance;
    /**
     * @var Amount
     */
    private $shipping_discount;
    /**
     * @var Amount
     */
    private $discount;

    /**
     * @return Amount
     */
    public function getItemTotal()
    {
        return $this->item_total;
    }

    /**
     * @param Amount $item_total
     *
     * @return self
     */
    public function setItemTotal(Amount $item_total)
    {
        $this->item_total = $item_total;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Amount $shipping
     *
     * @return self
     */
    public function setShipping(Amount $shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getHandling()
    {
        return $this->handling;
    }

    /**
     * @param Amount $handling
     *
     * @return self
     */
    public function setHandling(Amount $handling)
    {
        $this->handling = $handling;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }

    /**
     * @param Amount $tax_total
     *
     * @return self
     */
    public function setTaxTotal(Amount $tax_total)
    {
        $this->tax_total = $tax_total;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * @param Amount $insurance
     *
     * @return self
     */
    public function setInsurance(Amount $insurance)
    {
        $this->insurance = $insurance;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getShippingDiscount()
    {
        return $this->shipping_discount;
    }

    /**
     * @param Amount $shipping_discount
     *
     * @return self
     */
    public function setShippingDiscount(Amount $shipping_discount)
    {
        $this->shipping_discount = $shipping_discount;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param Amount $discount
     *
     * @return self
     */
    public function setDiscount(Amount $discount)
    {
        $this->discount = $discount;

        return $this;
    }
}
