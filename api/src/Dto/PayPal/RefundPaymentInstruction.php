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
 * Any additional payments instructions during refund payment processing. This object is only
 * applicable to merchants that have been enabled for PayPal Commerce Platform for Marketplaces and
 * Platforms capability. Please speak to your account manager if you want to use this capability.
 */
class RefundPaymentInstruction
{
    /**
     * @var RefundPlatformFee[]|null
     */
    private $platformFees;

    /**
     * Returns Platform Fees.
     * Specifies the amount that the API caller will contribute to the refund being processed. The amount
     * needs to be lower than platform_fees amount originally captured or the amount that is remaining if
     * multiple refunds have been processed. This field is only applicable to merchants that have been
     * enabled for PayPal Commerce Platform for Marketplaces and Platforms capability. Please speak to your
     * account manager if you want to use this capability.
     *
     * @return RefundPlatformFee[]|null
     */
    public function getPlatformFees(): ?array
    {
        return $this->platformFees;
    }

    /**
     * Sets Platform Fees.
     * Specifies the amount that the API caller will contribute to the refund being processed. The amount
     * needs to be lower than platform_fees amount originally captured or the amount that is remaining if
     * multiple refunds have been processed. This field is only applicable to merchants that have been
     * enabled for PayPal Commerce Platform for Marketplaces and Platforms capability. Please speak to your
     * account manager if you want to use this capability.
     *
     * @maps platform_fees
     *
     * @param RefundPlatformFee[]|null $platformFees
     * @return self
     */
    public function setPlatformFees(?array $platformFees): self
    {
        $this->platformFees = $platformFees;

        return $this;
    }
}
