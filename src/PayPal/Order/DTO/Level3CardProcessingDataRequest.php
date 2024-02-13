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

class Level3CardProcessingDataRequest
{
    /**
     * @var Amount
     */
    private $shipping_amount;
    /**
     * @var Amount
     */
    private $duty_amount;
    /**
     * @var Amount
     */
    private $discount_amount;
    /**
     * @var AddressRequest
     */
    private $shipping_address;
    /**
     * @var string
     */
    private $ships_from_postal_code;
    /**
     * @var LineItemRequest[]
     */
    private $line_items;

    /**
     * @return Amount
     */
    public function getShippingAmount()
    {
        return $this->shipping_amount;
    }

    /**
     * @param Amount $shipping_amount
     *
     * @return void
     */
    public function setShippingAmount(Amount $shipping_amount)
    {
        $this->shipping_amount = $shipping_amount;
    }

    /**
     * @return Amount
     */
    public function getDutyAmount()
    {
        return $this->duty_amount;
    }

    /**
     * @param Amount $duty_amount
     *
     * @return void
     */
    public function setDutyAmount(Amount $duty_amount)
    {
        $this->duty_amount = $duty_amount;
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
     * @return void
     */
    public function setDiscountAmount(Amount $discount_amount)
    {
        $this->discount_amount = $discount_amount;
    }

    /**
     * @return AddressRequest
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @param AddressRequest $shipping_address
     *
     * @return void
     */
    public function setShippingAddress(AddressRequest $shipping_address)
    {
        $this->shipping_address = $shipping_address;
    }

    /**
     * @return string
     */
    public function getShipsFromPostalCode()
    {
        return $this->ships_from_postal_code;
    }

    /**
     * @param string $ships_from_postal_code
     *
     * @return void
     */
    public function setShipsFromPostalCode($ships_from_postal_code)
    {
        $this->ships_from_postal_code = $ships_from_postal_code;
    }

    /**
     * @return LineItemRequest[]
     */
    public function getLineItems()
    {
        return $this->line_items;
    }

    /**
     * @param LineItemRequest[] $line_items
     *
     * @return void
     */
    public function setLineItems(array $line_items)
    {
        $this->line_items = $line_items;
    }
}
