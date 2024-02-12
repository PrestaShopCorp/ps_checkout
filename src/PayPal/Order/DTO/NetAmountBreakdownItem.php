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

class NetAmountBreakdownItem
{
    /**
     * @var Amount|null
     */
    protected $payable_amount;

    /**
     * @var Amount|null
     */
    protected $converted_amount;

    /**
     * @var ExchangeRate|null
     */
    protected $exchange_rate;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->payable_amount = isset($data['payable_amount']) ? $data['payable_amount'] : null;
        $this->converted_amount = isset($data['converted_amount']) ? $data['converted_amount'] : null;
        $this->exchange_rate = isset($data['exchange_rate']) ? $data['exchange_rate'] : null;
    }

    /**
     * Gets payable_amount.
     *
     * @return Amount|null
     */
    public function getPayableAmount()
    {
        return $this->payable_amount;
    }

    /**
     * Sets payable_amount.
     *
     * @param Amount|null $payable_amount
     *
     * @return $this
     */
    public function setPayableAmount(Amount $payable_amount = null)
    {
        $this->payable_amount = $payable_amount;

        return $this;
    }

    /**
     * Gets converted_amount.
     *
     * @return Amount|null
     */
    public function getConvertedAmount()
    {
        return $this->converted_amount;
    }

    /**
     * Sets converted_amount.
     *
     * @param Amount|null $converted_amount
     *
     * @return $this
     */
    public function setConvertedAmount(Amount $converted_amount = null)
    {
        $this->converted_amount = $converted_amount;

        return $this;
    }

    /**
     * Gets exchange_rate.
     *
     * @return ExchangeRate|null
     */
    public function getExchangeRate()
    {
        return $this->exchange_rate;
    }

    /**
     * Sets exchange_rate.
     *
     * @param ExchangeRate|null $exchange_rate
     *
     * @return $this
     */
    public function setExchangeRate(ExchangeRate $exchange_rate = null)
    {
        $this->exchange_rate = $exchange_rate;

        return $this;
    }
}
