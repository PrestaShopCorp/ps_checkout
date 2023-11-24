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

namespace PrestaShop\Module\PrestashopCheckout\Product;

use PrestaShop\Module\PrestashopCheckout\Amount\Amount;

class Product
{
    /** @var string */
    private $name;

    /** @var Amount */
    private $unitPrice;

    /** @var int */
    private $quantity;

    /** @var boolean */
    private $isInStock;

    /** @var boolean */
    private $isAvailableForOrder;

    /** @var string */
    private $sku;

    /** @var string */
    private $category;

    /** @var Amount */
    private $tax;

    public function __construct($name, $unitPrice, $quantity, $isInStock, $isAvailableForOrder, $sku = '', $category = '', $tax = null)
    {
        $this->name = $name;
        $this->unitPrice = $unitPrice;
        $this->quantity = $quantity;
        $this->isInStock = $isInStock;
        $this->isAvailableForOrder = $isAvailableForOrder;
        $this->sku = $sku;
        $this->category = $category;
        $this->tax = $tax;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Amount
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return bool
     */
    public function isInStock()
    {
        return $this->isInStock;
    }

    /**
     * @return bool
     */
    public function isAvailableForOrder()
    {
        return $this->isAvailableForOrder;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return Amount
     */
    public function getTax()
    {
        return $this->tax;
    }
}
