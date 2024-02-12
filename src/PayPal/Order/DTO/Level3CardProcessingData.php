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

class Level3CardProcessingData
{
    /**
     * @var Amount|null
     */
    protected $shipping_amount;
    /**
     * @var Amount|null
     */
    protected $duty_amount;
    /**
     * @var Amount|null
     */
    protected $discount_amount;
    /**
     * @var AddressRequest|null
     */
    protected $shipping_address;
    /**
     * Use this field to specify the postal code of the shipping location.
     *
     * @var string|null
     */
    protected $ships_from_postal_code;
    /**
     * A list of the items that were purchased with this payment. If your merchant account has been configured for Level 3 processing this field will be passed to the processor on your behalf.
     *
     * @var LineItem[]|null
     */
    protected $line_items;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->shipping_amount = isset($data['shipping_amount']) ? $data['shipping_amount'] : null;
        $this->duty_amount = isset($data['duty_amount']) ? $data['duty_amount'] : null;
        $this->discount_amount = isset($data['discount_amount']) ? $data['discount_amount'] : null;
        $this->shipping_address = isset($data['shipping_address']) ? $data['shipping_address'] : null;
        $this->ships_from_postal_code = isset($data['ships_from_postal_code']) ? $data['ships_from_postal_code'] : null;
        $this->line_items = isset($data['line_items']) ? $data['line_items'] : null;
    }

    /**
     * Gets shipping_amount.
     *
     * @return Amount|null
     */
    public function getShippingAmount()
    {
        return $this->shipping_amount;
    }

    /**
     * Sets shipping_amount.
     *
     * @param Amount|null $shipping_amount
     *
     * @return $this
     */
    public function setShippingAmount(Amount $shipping_amount = null)
    {
        $this->shipping_amount = $shipping_amount;

        return $this;
    }

    /**
     * Gets duty_amount.
     *
     * @return Amount|null
     */
    public function getDutyAmount()
    {
        return $this->duty_amount;
    }

    /**
     * Sets duty_amount.
     *
     * @param Amount|null $duty_amount
     *
     * @return $this
     */
    public function setDutyAmount(Amount $duty_amount = null)
    {
        $this->duty_amount = $duty_amount;

        return $this;
    }

    /**
     * Gets discount_amount.
     *
     * @return Amount|null
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    /**
     * Sets discount_amount.
     *
     * @param Amount|null $discount_amount
     *
     * @return $this
     */
    public function setDiscountAmount(Amount $discount_amount = null)
    {
        $this->discount_amount = $discount_amount;

        return $this;
    }

    /**
     * Gets shipping_address.
     *
     * @return AddressRequest|null
     */
    public function getShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * Sets shipping_address.
     *
     * @param AddressRequest|null $shipping_address
     *
     * @return $this
     */
    public function setShippingAddress(AddressRequest $shipping_address = null)
    {
        $this->shipping_address = $shipping_address;

        return $this;
    }

    /**
     * Gets ships_from_postal_code.
     *
     * @return string|null
     */
    public function getShipsFromPostalCode()
    {
        return $this->ships_from_postal_code;
    }

    /**
     * Sets ships_from_postal_code.
     *
     * @param string|null $ships_from_postal_code use this field to specify the postal code of the shipping location
     *
     * @return $this
     */
    public function setShipsFromPostalCode($ships_from_postal_code = null)
    {
        $this->ships_from_postal_code = $ships_from_postal_code;

        return $this;
    }

    /**
     * Gets line_items.
     *
     * @return LineItem[]|null
     */
    public function getLineItems()
    {
        return $this->line_items;
    }

    /**
     * Sets line_items.
     *
     * @param LineItem[]|null $line_items A list of the items that were purchased with this payment. If your merchant account has been configured for Level 3 processing this field will be passed to the processor on your behalf.
     *
     * @return $this
     */
    public function setLineItems(array $line_items = null)
    {
        $this->line_items = $line_items;

        return $this;
    }
}
