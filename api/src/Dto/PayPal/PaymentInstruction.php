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
 * Any additional payment instructions to be consider during payment processing. This processing
 * instruction is applicable for Capturing an order or Authorizing an Order.
 */
class PaymentInstruction
{
    /**
     * @var PlatformFee[]|null
     */
    private $platformFees;

    /**
     * @var string|null
     */
    private $disbursementMode = DisbursementMode::INSTANT;

    /**
     * @var string|null
     */
    private $payeePricingTierId;

    /**
     * @var string|null
     */
    private $payeeReceivableFxRateId;

    /**
     * Returns Platform Fees.
     * An array of various fees, commissions, tips, or donations. This field is only applicable to
     * merchants that been enabled for PayPal Complete Payments Platform for Marketplaces and Platforms
     * capability.
     *
     * @return PlatformFee[]|null
     */
    public function getPlatformFees(): ?array
    {
        return $this->platformFees;
    }

    /**
     * Sets Platform Fees.
     * An array of various fees, commissions, tips, or donations. This field is only applicable to
     * merchants that been enabled for PayPal Complete Payments Platform for Marketplaces and Platforms
     * capability.
     *
     * @maps platform_fees
     *
     * @param PlatformFee[]|null $platformFees
     */
    public function setPlatformFees(?array $platformFees): void
    {
        $this->platformFees = $platformFees;
    }

    /**
     * Returns Disbursement Mode.
     * The funds that are held on behalf of the merchant.
     */
    public function getDisbursementMode(): ?string
    {
        return $this->disbursementMode;
    }

    /**
     * Sets Disbursement Mode.
     * The funds that are held on behalf of the merchant.
     *
     * @maps disbursement_mode
     */
    public function setDisbursementMode(?string $disbursementMode): void
    {
        $this->disbursementMode = $disbursementMode;
    }

    /**
     * Returns Payee Pricing Tier Id.
     * This field is only enabled for selected merchants/partners to use and provides the ability to
     * trigger a specific pricing rate/plan for a payment transaction. The list of eligible
     * 'payee_pricing_tier_id' would be provided to you by your Account Manager. Specifying values other
     * than the one provided to you by your account manager would result in an error.
     */
    public function getPayeePricingTierId(): ?string
    {
        return $this->payeePricingTierId;
    }

    /**
     * Sets Payee Pricing Tier Id.
     * This field is only enabled for selected merchants/partners to use and provides the ability to
     * trigger a specific pricing rate/plan for a payment transaction. The list of eligible
     * 'payee_pricing_tier_id' would be provided to you by your Account Manager. Specifying values other
     * than the one provided to you by your account manager would result in an error.
     *
     * @maps payee_pricing_tier_id
     */
    public function setPayeePricingTierId(?string $payeePricingTierId): void
    {
        $this->payeePricingTierId = $payeePricingTierId;
    }

    /**
     * Returns Payee Receivable Fx Rate Id.
     * FX identifier generated returned by PayPal to be used for payment processing in order to honor FX
     * rate (for eligible integrations) to be used when amount is settled/received into the payee account.
     */
    public function getPayeeReceivableFxRateId(): ?string
    {
        return $this->payeeReceivableFxRateId;
    }

    /**
     * Sets Payee Receivable Fx Rate Id.
     * FX identifier generated returned by PayPal to be used for payment processing in order to honor FX
     * rate (for eligible integrations) to be used when amount is settled/received into the payee account.
     *
     * @maps payee_receivable_fx_rate_id
     */
    public function setPayeeReceivableFxRateId(?string $payeeReceivableFxRateId): void
    {
        $this->payeeReceivableFxRateId = $payeeReceivableFxRateId;
    }
}
