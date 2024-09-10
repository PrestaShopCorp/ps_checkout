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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\DTO;

use DateTime;

class ApplePayLineItem
{
    const TYPE_PENDING = 'pending';
    const TYPE_FINAL = 'final';
    const PAYMENT_TIMING_IMMEDIATE = 'immediate';
    const PAYMENT_TIMING_RECURRING = 'recurring';
    const PAYMENT_TIMING_DEFERRED = 'deferred';
    const PAYMENT_TIMING_AUTOMATIC_RELOAD = 'automaticReload';
    const RECURRING_PAYMENT_INTERVAL_UNIT_DAY = 'day';
    const RECURRING_PAYMENT_INTERVAL_UNIT_WEEK = 'week';
    const RECURRING_PAYMENT_INTERVAL_UNIT_MONTH = 'month';
    const RECURRING_PAYMENT_INTERVAL_UNIT_YEAR = 'year';

    /**
     * @var self::TYPE_PENDING|self::TYPE_FINAL
     */
    private $type = self::TYPE_FINAL;
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $amount;
    /**
     * @var self::PAYMENT_TIMING_IMMEDIATE|self::PAYMENT_TIMING_RECURRING|self::PAYMENT_TIMING_DEFERRED|self::PAYMENT_TIMING_AUTOMATIC_RELOAD
     */
    private $paymentTiming;
    /**
     * @var DateTime|null
     */
    private $recurringPaymentStartDate = null;
    /**
     * @var self::RECURRING_PAYMENT_INTERVAL_UNIT_DAY|self::RECURRING_PAYMENT_INTERVAL_UNIT_WEEK|self::RECURRING_PAYMENT_INTERVAL_UNIT_MONTH|self::RECURRING_PAYMENT_INTERVAL_UNIT_YEAR
     */
    private $recurringPaymentIntervalUnit;
    /**
     * @var int
     */
    private $recurringPaymentIntervalCount;
    /**
     * @var DateTime|null
     */
    private $recurringPaymentEndDate = null;
    /**
     * @var DateTime|null
     */
    private $deferredPaymentDate = null;
    /**
     * @var string
     */
    private $automaticReloadPaymentThresholdAmount;

    /**
     * @param self::TYPE_PENDING|self::TYPE_FINAL $type
     *
     * @return ApplePayLineItem
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
     * @param string $label
     *
     * @return ApplePayLineItem
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
     * @param string $amount
     *
     * @return ApplePayLineItem
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param self::PAYMENT_TIMING_IMMEDIATE|self::PAYMENT_TIMING_RECURRING|self::PAYMENT_TIMING_DEFERRED|self::PAYMENT_TIMING_AUTOMATIC_RELOAD $paymentTiming
     *
     * @return ApplePayLineItem
     */
    public function setPaymentTiming($paymentTiming)
    {
        $this->paymentTiming = $paymentTiming;

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentTiming()
    {
        return $this->paymentTiming;
    }

    /**
     * @param DateTime $recurringPaymentStartDate
     *
     * @return ApplePayLineItem
     */
    public function setRecurringPaymentStartDate($recurringPaymentStartDate)
    {
        $this->recurringPaymentStartDate = $recurringPaymentStartDate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRecurringPaymentStartDate()
    {
        return $this->recurringPaymentStartDate;
    }

    /**
     * @param self::RECURRING_PAYMENT_INTERVAL_UNIT_DAY|self::RECURRING_PAYMENT_INTERVAL_UNIT_WEEK|self::RECURRING_PAYMENT_INTERVAL_UNIT_MONTH|self::RECURRING_PAYMENT_INTERVAL_UNIT_YEAR $recurringPaymentIntervalUnit
     *
     * @return ApplePayLineItem
     */
    public function setRecurringPaymentIntervalUnit($recurringPaymentIntervalUnit)
    {
        $this->recurringPaymentIntervalUnit = $recurringPaymentIntervalUnit;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecurringPaymentIntervalUnit()
    {
        return $this->recurringPaymentIntervalUnit;
    }

    /**
     * @param int $recurringPaymentIntervalCount
     *
     * @return ApplePayLineItem
     */
    public function setRecurringPaymentIntervalCount($recurringPaymentIntervalCount)
    {
        $this->recurringPaymentIntervalCount = $recurringPaymentIntervalCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRecurringPaymentIntervalCount()
    {
        return $this->recurringPaymentIntervalCount;
    }

    /**
     * @param DateTime $recurringPaymentEndDate
     *
     * @return ApplePayLineItem
     */
    public function setRecurringPaymentEndDate($recurringPaymentEndDate)
    {
        $this->recurringPaymentEndDate = $recurringPaymentEndDate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getRecurringPaymentEndDate()
    {
        return $this->recurringPaymentEndDate;
    }

    /**
     * @param DateTime $deferredPaymentDate
     *
     * @return ApplePayLineItem
     */
    public function setDeferredPaymentDate($deferredPaymentDate)
    {
        $this->deferredPaymentDate = $deferredPaymentDate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDeferredPaymentDate()
    {
        return $this->deferredPaymentDate;
    }

    /**
     * @param string $automaticReloadPaymentThresholdAmount
     *
     * @return ApplePayLineItem
     */
    public function setAutomaticReloadPaymentThresholdAmount($automaticReloadPaymentThresholdAmount)
    {
        $this->automaticReloadPaymentThresholdAmount = $automaticReloadPaymentThresholdAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getAutomaticReloadPaymentThresholdAmount()
    {
        return $this->automaticReloadPaymentThresholdAmount;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'type' => $this->type,
            'label' => $this->label,
            'amount' => $this->amount,
            'paymentTiming' => $this->paymentTiming,
            'recurringPaymentStartDate' => $this->recurringPaymentStartDate ? $this->recurringPaymentStartDate->format(DateTime::ATOM) : null,
            'recurringPaymentIntervalUnit' => $this->recurringPaymentIntervalUnit,
            'recurringPaymentIntervalCount' => $this->recurringPaymentIntervalCount,
            'recurringPaymentEndDate' => $this->recurringPaymentEndDate ? $this->recurringPaymentEndDate->format(DateTime::ATOM) : null,
            'deferredPaymentDate' => $this->deferredPaymentDate ? $this->deferredPaymentDate->format(DateTime::ATOM) : null,
            'automaticReloadPaymentThresholdAmount' => $this->automaticReloadPaymentThresholdAmount,
        ]);
    }
}
