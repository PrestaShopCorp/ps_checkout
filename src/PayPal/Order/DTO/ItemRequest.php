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

class ItemRequest
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var Amount
     */
    private $unit_amount;
    /**
     * @var Amount
     */
    private $tax;
    /**
     * @var string
     */
    private $quantity;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $sku;
    /**
     * @var string
     */
    private $category;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Amount
     */
    public function getUnitAmount()
    {
        return $this->unit_amount;
    }

    /**
     * @param Amount $unit_amount
     *
     * @return void
     */
    public function setUnitAmount(Amount $unit_amount)
    {
        $this->unit_amount = $unit_amount;
    }

    /**
     * @return Amount
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param Amount $tax
     *
     * @return void
     */
    public function setTax(Amount $tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     *
     * @return void
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return void
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return void
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }
}
