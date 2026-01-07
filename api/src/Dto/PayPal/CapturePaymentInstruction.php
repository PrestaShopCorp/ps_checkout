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
class CapturePaymentInstruction
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
    private $payeeReceivableFxRateId;

    /**
     * Returns Platform Fees.
     * An array of platform or partner fees, commissions, or brokerage fees that associated with the
     * captured payment.
     *
     * @return PlatformFee[]|null
     */
    public function getPlatformFees(): ?array
    {
        return $this->platformFees;
    }

    /**
     * Sets Platform Fees.
     * An array of platform or partner fees, commissions, or brokerage fees that associated with the
     * captured payment.
     *
     * @maps platform_fees
     *
     * @param PlatformFee[]|null $platformFees
     * @return self
     */
    public function setPlatformFees(?array $platformFees): self
    {
        $this->platformFees = $platformFees;

        return $this;
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
     * @return self
     */
    public function setDisbursementMode(?string $disbursementMode): self
    {
        $this->disbursementMode = $disbursementMode;

        return $this;
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
     * @return self
     */
    public function setPayeeReceivableFxRateId(?string $payeeReceivableFxRateId): self
    {
        $this->payeeReceivableFxRateId = $payeeReceivableFxRateId;

        return $this;
    }
}
