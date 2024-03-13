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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Query;

class GetPayPalRefundForPDFOrderSlipQueryResult
{
    /** @var int */
    private $paypalRefundId;

    /** @var string */
    private $paypalRefundAmount;

    /** @var string */
    private $paypalRefundCurrency;

    /** @var int */
    private $paypalRefundCurrencyId;

    /** @var string */
    private $paypalRefundStatus;

    /** @var string */
    private $paypalRefundNote;

    /** @var string */
    private $paypalRefundCreateTime;

    /** @var string */
    private $paypalRefundUpdateTime;

    /**
     * @param int $paypalRefundId
     * @param string $paypalRefundAmount
     * @param string $paypalRefundCurrency
     * @param int $paypalRefundCurrencyId
     * @param string $paypalRefundStatus
     * @param string $paypalRefundNote
     * @param string $paypalRefundCreateTime
     * @param string $paypalRefundUpdateTime
     */
    public function __construct(
        $paypalRefundId,
        $paypalRefundAmount,
        $paypalRefundCurrency,
        $paypalRefundCurrencyId,
        $paypalRefundStatus,
        $paypalRefundNote,
        $paypalRefundCreateTime,
        $paypalRefundUpdateTime
    ) {
        $this->paypalRefundId = $paypalRefundId;
        $this->paypalRefundAmount = $paypalRefundAmount;
        $this->paypalRefundCurrency = $paypalRefundCurrency;
        $this->paypalRefundCurrencyId = $paypalRefundCurrencyId;
        $this->paypalRefundStatus = $paypalRefundStatus;
        $this->paypalRefundNote = $paypalRefundNote;
        $this->paypalRefundCreateTime = $paypalRefundCreateTime;
        $this->paypalRefundUpdateTime = $paypalRefundUpdateTime;
    }

    /**
     * @return int
     */
    public function getPaypalRefundId()
    {
        return $this->paypalRefundId;
    }

    /**
     * @return string
     */
    public function getPaypalRefundAmount()
    {
        return $this->paypalRefundAmount;
    }

    /**
     * @return string
     */
    public function getPaypalRefundCurrency()
    {
        return $this->paypalRefundCurrency;
    }

    /**
     * @return int
     */
    public function getPaypalRefundCurrencyId()
    {
        return $this->paypalRefundCurrencyId;
    }

    /**
     * @return string
     */
    public function getPaypalRefundStatus()
    {
        return $this->paypalRefundStatus;
    }

    /**
     * @return string
     */
    public function getPaypalRefundNote()
    {
        return $this->paypalRefundNote;
    }

    /**
     * @return string
     */
    public function getPaypalRefundCreateTime()
    {
        return $this->paypalRefundCreateTime;
    }

    /**
     * @return string
     */
    public function getPaypalRefundUpdateTime()
    {
        return $this->paypalRefundUpdateTime;
    }
}
