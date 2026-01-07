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
 * Metadata for merchant-managed recurring billing plans. Valid only during the saved payment method
 * token or billing agreement creation.
 */
class OrderBillingPlan
{
    /**
     * @var BillingCycle[]
     */
    private $billingCycles;

    /**
     * @var Money|null
     */
    private $setupFee;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @param BillingCycle[] $billingCycles
     */
    public function __construct(array $billingCycles)
    {
        $this->billingCycles = $billingCycles;
    }

    /**
     * Returns Billing Cycles.
     * An array of billing cycles for trial billing and regular billing. A plan can have at most two trial
     * cycles and only one regular cycle.
     *
     * @return BillingCycle[]
     */
    public function getBillingCycles(): array
    {
        return $this->billingCycles;
    }

    /**
     * Sets Billing Cycles.
     * An array of billing cycles for trial billing and regular billing. A plan can have at most two trial
     * cycles and only one regular cycle.
     *
     * @required
     * @maps billing_cycles
     *
     * @param BillingCycle[] $billingCycles
     * @return self
     */
    public function setBillingCycles(array $billingCycles): self
    {
        $this->billingCycles = $billingCycles;

        return $this;
    }

    /**
     * Returns Setup Fee.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getSetupFee(): ?Money
    {
        return $this->setupFee;
    }

    /**
     * Sets Setup Fee.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps setup_fee
     * @return self
     */
    public function setSetupFee(?Money $setupFee): self
    {
        $this->setupFee = $setupFee;

        return $this;
    }

    /**
     * Returns Name.
     * Name of the recurring plan.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * Name of the recurring plan.
     *
     * @maps name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
