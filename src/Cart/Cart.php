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

namespace PrestaShop\Module\PrestashopCheckout\Cart;

use _PHPStan_446ead745\Nette\Neon\Exception;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\Discount\Discount;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Exception\CountryException;
use PrestaShop\Module\PrestashopCheckout\Product\Product;

class Cart
{

    /** @var CartId */
    private $cartId;
    /** @var string */
    private $total;

    /** @var string */
    private $total_wt_taxes;

    /** @var array<Product> */
    private $products;

    /** @var array<Discount> */
    private $discount;

    /**
     * @param int $cartId
     * @param string $total
     * @param string $total_wt_taxes
     * @param Product[] $products
     * @param Discount[] $discount
     * @throws CartException
     */
    public function __construct($cartId,$total, $total_wt_taxes, array $products, array $discount)
    {
        $this->cartId = new CartId($cartId);
        $this->total = $this->assertValidTotal($total);
        $this->total_wt_taxes = $this->assertValidTotalWtTaxes($total_wt_taxes);
        $this->products = $this->assertValidProducts($products);
        $this->discount = $this->assertValidDiscounts($discount);
    }

    /**
     * @return string
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param string $total
     */
    public function setTotal($total)
    {
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getTotalWtTaxes()
    {
        return $this->total_wt_taxes;
    }

    /**
     * @param string $total_wt_taxes
     */
    public function setTotalWtTaxes($total_wt_taxes)
    {
        $this->total_wt_taxes = $total_wt_taxes;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Product[] $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @return Discount[]
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param Discount[] $discount
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    }

    /**
     * @param $total
     * @return string
     * @throws CartException
     */
    private function assertValidTotal($total){
        if(!is_string($total))
        {
            throw new CartException(sprintf('TOTAL is not a string (%s)', gettype($total)),CartException::WRONG_TYPE_TOTAL);
        }
        if(!is_numeric($total))
        {
            throw new CartException('TOTAL is not numeric',CartException::WRONG_TYPE_TOTAL);
        }
        return $total;
    }

    /**
     * @param $totalWtTaxes
     * @return string
     * @throws CartException
     */
    private function assertValidTotalWtTaxes($totalWtTaxes){
        if(!is_string($totalWtTaxes))
        {
            throw new CartException(sprintf('TOTAL WT TAXES is not a string (%s)', gettype($totalWtTaxes)),CartException::WRONG_TYPE_TOTAL_WT_TAXES);
        }
        if(!is_numeric($totalWtTaxes))
        {
            throw new CartException('TOTAL WT TAXES is not numeric',CartException::WRONG_TYPE_TOTAL_WT_TAXES);
        }
        return $totalWtTaxes;
    }

    /**
     * @param $products
     * @return array
     * @throws CartException
     */
    private function assertValidProducts($products){
        if(!is_array($products))
        {
            throw new CartException(sprintf('PRODUCTS is not an array (%s)', gettype($products)),CartException::WRONG_TYPE_PRODUCTS);
        }
        foreach ($products as $product) {
            if(gettype($product) === Product::class)
            {
                throw new CartException(sprintf('PRODUCT is not a product (%s)',gettype($product)),CartException::WRONG_TYPE_PRODUCT);
            }
        }
        return $products;
    }

    /**
     * @param $discounts
     * @return array
     * @throws CartException
     */
    private function assertValidDiscounts($discounts){
        if(!is_array($discounts))
        {
            throw new CartException(sprintf('DISCOUNTS is not an array (%s)', gettype($discounts)),CartException::WRONG_TYPE_DISCOUNTS);
        }
        foreach ($discounts as $discount) {
            if(gettype($discount) === Discount::class)
            {
                throw new CartException(sprintf('DISCOUNT is not a discount (%s)',gettype($discount)),CartException::WRONG_TYPE_DISCOUNT);
            }
        }
        return $discounts;
    }
}
