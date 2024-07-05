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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO;

class GooglePayDisplayItem
{
    const TYPE_LINE_ITEM = 'LINE_ITEM';
    const TYPE_SUBTOTAL = 'SUBTOTAL';
    const TYPE_TAX = 'TAX';
    const STATUS_FINAL = 'FINAL';
    const STATUS_PENDING = 'PENDING';

    /**
     * @var string
     */
    private $label;
    /**
     * @var 'LINE_ITEM'|'SUBTOTAL'|'TAX'
     */
    private $type;
    /**
     * @var string
     */
    private $price;
    /**
     * @var 'FINAL'|'PENDING'
     */
    private $status = self::STATUS_FINAL;

    /**
     * @param string $label
     *
     * @return GooglePayDisplayItem
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param 'LINE_ITEM'|'SUBTOTAL'|'TAX' $type
     *
     * @return GooglePayDisplayItem
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $price
     *
     * @return GooglePayDisplayItem
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param 'FINAL'|'PENDING' $status
     *
     * @return GooglePayDisplayItem
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function toArray()
    {
        return array_filter([
            'label' => $this->label,
            'type' => $this->type,
            'price' => $this->price,
            'status' => $this->status,
        ]);
    }
}
