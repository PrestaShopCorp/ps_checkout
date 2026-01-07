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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The billing cycle providing details of the billing frequency, amount, duration and if the billing
 * cycle is a free, discounted or regular billing cycle. The sequence of the billing cycle will be in
 * the following order - free trial billing cycle(s), discounted trial billing cycle(s), regular
 * billing cycle(s).
 */
class BillingCycle
{
    /**
     * @var string
     */
    private $tenureType;

    /**
     * @var PricingScheme|null
     */
    private $pricingScheme;

    /**
     * @var int|null
     */
    private $totalCycles = 1;

    /**
     * @var int|null
     */
    private $sequence = 1;

    /**
     * @var string|null
     */
    private $startDate;

    /**
     * @param string $tenureType
     */
    public function __construct(string $tenureType)
    {
        $this->tenureType = $tenureType;
    }

    /**
     * Returns Tenure Type.
     * The tenure type of the billing cycle identifies if the billing cycle is a trial(free or discounted)
     * or regular billing cycle.
     */
    public function getTenureType(): string
    {
        return $this->tenureType;
    }

    /**
     * Sets Tenure Type.
     * The tenure type of the billing cycle identifies if the billing cycle is a trial(free or discounted)
     * or regular billing cycle.
     *
     * @required
     * @maps tenure_type
     * @return self
     */
    public function setTenureType(string $tenureType): self
    {
        $this->tenureType = $tenureType;

        return $this;
    }

    /**
     * Returns Pricing Scheme.
     * The pricing scheme details.
     */
    public function getPricingScheme(): ?PricingScheme
    {
        return $this->pricingScheme;
    }

    /**
     * Sets Pricing Scheme.
     * The pricing scheme details.
     *
     * @maps pricing_scheme
     * @return self
     */
    public function setPricingScheme(?PricingScheme $pricingScheme): self
    {
        $this->pricingScheme = $pricingScheme;

        return $this;
    }

    /**
     * Returns Total Cycles.
     * The number of times this billing cycle gets executed. Trial billing cycles can only be executed a
     * finite number of times (value between 1 and 999 for total_cycles). Regular billing cycles can be
     * executed infinite times (value of 0 for total_cycles) or a finite number of times (value between 1
     * and 999 for total_cycles).
     */
    public function getTotalCycles(): ?int
    {
        return $this->totalCycles;
    }

    /**
     * Sets Total Cycles.
     * The number of times this billing cycle gets executed. Trial billing cycles can only be executed a
     * finite number of times (value between 1 and 999 for total_cycles). Regular billing cycles can be
     * executed infinite times (value of 0 for total_cycles) or a finite number of times (value between 1
     * and 999 for total_cycles).
     *
     * @maps total_cycles
     * @return self
     */
    public function setTotalCycles(?int $totalCycles): self
    {
        $this->totalCycles = $totalCycles;

        return $this;
    }

    /**
     * Returns Sequence.
     * The order in which this cycle is to run among other billing cycles. For example, a trial billing
     * cycle has a `sequence` of `1` while a regular billing cycle has a `sequence` of `2`, so that trial
     * cycle runs before the regular cycle.
     */
    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    /**
     * Sets Sequence.
     * The order in which this cycle is to run among other billing cycles. For example, a trial billing
     * cycle has a `sequence` of `1` while a regular billing cycle has a `sequence` of `2`, so that trial
     * cycle runs before the regular cycle.
     *
     * @maps sequence
     * @return self
     */
    public function setSequence(?int $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Returns Start Date.
     * The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-
     * 5.6). To represent special legal values, such as a date of birth, you should use dates with no
     * associated time or time-zone data. Whenever possible, use the standard `date_time` type. This
     * regular expression does not validate all dates. For example, February 31 is valid and nothing is
     * known about leap years.
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * Sets Start Date.
     * The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-
     * 5.6). To represent special legal values, such as a date of birth, you should use dates with no
     * associated time or time-zone data. Whenever possible, use the standard `date_time` type. This
     * regular expression does not validate all dates. For example, February 31 is valid and nothing is
     * known about leap years.
     *
     * @maps start_date
     * @return self
     */
    public function setStartDate(?string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }
}
