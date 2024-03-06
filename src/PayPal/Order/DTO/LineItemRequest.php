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

class LineItemRequest extends ItemRequest
{
    /**
     * @var string
     */
    private $commodity_code;
    /**
     * @var Amount
     */
    private $discount_amount;
    /**
     * @var Amount
     */
    private $total_amount;
    /**
     * @var string
     */
    private $unit_of_measure;

    /**
     * @return string
     */
    public function getCommodityCode()
    {
        return $this->commodity_code;
    }

    /**
     * @param string $commodity_code
     *
     * @return $this
     */
    public function setCommodityCode($commodity_code)
    {
        $this->commodity_code = $commodity_code;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    /**
     * @param Amount $discount_amount
     *
     * @return $this
     */
    public function setDiscountAmount(Amount $discount_amount)
    {
        $this->discount_amount = $discount_amount;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * @param Amount $total_amount
     *
     * @return $this
     */
    public function setTotalAmount(Amount $total_amount)
    {
        $this->total_amount = $total_amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getUnitOfMeasure()
    {
        return $this->unit_of_measure;
    }

    /**
     * @param string $unit_of_measure
     *
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure)
    {
        $this->unit_of_measure = $unit_of_measure;

        return $this;
    }
}
