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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Entities;

class AmountBreakdown
{
    /** @var Money */
    private $discount;

    /** @var Money */
    private $handling;

    /** @var Money */
    private $insurance;

    /** @var Money */
    private $itemTotal;

    /** @var Money */
    private $shipping;

    /** @var Money */
    private $shippingDiscount;

    /** @var Money */
    private $taxTotal;

    /**
     * @return Money
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param Money $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @return Money
     */
    public function getHandling()
    {
        return $this->handling;
    }

    /**
     * @param Money $handling
     */
    public function setHandling($handling)
    {
        $this->handling = $handling;
    }

    /**
     * @return Money
     */
    public function getInsurance()
    {
        return $this->insurance;
    }

    /**
     * @param Money $insurance
     */
    public function setInsurance($insurance)
    {
        $this->insurance = $insurance;
    }

    /**
     * @return Money
     */
    public function getItemTotal()
    {
        return $this->itemTotal;
    }

    /**
     * @param Money $itemTotal
     */
    public function setItemTotal($itemTotal)
    {
        $this->itemTotal = $itemTotal;
    }

    /**
     * @return Money
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Money $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return Money
     */
    public function getShippingDiscount()
    {
        return $this->shippingDiscount;
    }

    /**
     * @param Money $shippingDiscount
     */
    public function setShippingDiscount($shippingDiscount)
    {
        $this->shippingDiscount = $shippingDiscount;
    }

    /**
     * @return Money
     */
    public function getTaxTotal()
    {
        return $this->taxTotal;
    }

    /**
     * @param Money $taxTotal
     */
    public function setTaxTotal($taxTotal)
    {
        $this->taxTotal = $taxTotal;
    }

    public function toArray()
    {
        $data = [];

        if (!empty($this->getDiscount())) {
            $data['discount'] = $this->getDiscount()->toArray();
        }

        if (!empty($this->getHandling())) {
            $data['handling'] = $this->getHandling()->toArray();
        }

        if (!empty($this->getInsurance())) {
            $data['insurance'] = $this->getInsurance()->toArray();
        }

        if (!empty($this->getItemTotal())) {
            $data['item_total'] = $this->getItemTotal()->toArray();
        }

        if (!empty($this->getShipping())) {
            $data['shipping'] = $this->getShipping()->toArray();
        }

        if (!empty($this->getShippingDiscount())) {
            $data['shipping_discount'] = $this->getShippingDiscount()->toArray();
        }

        if (!empty($this->getTaxTotal())) {
            $data['tax_total'] = $this->getTaxTotal()->toArray();
        }

        return array_filter($data);
    }
}
