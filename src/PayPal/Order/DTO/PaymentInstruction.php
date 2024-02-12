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

class PaymentInstruction
{
    /**
     * An array of various fees, commissions, tips, or donations. This field is only applicable to merchants that been enabled for PayPal Commerce Platform for Marketplaces and Platforms capability.
     *
     * @var PlatformFee[]|null
     */
    protected $platform_fees;

    /**
     * @var string|null
     */
    protected $disbursement_mode;

    /**
     * This field is only enabled for selected merchants/partners to use and provides the ability to trigger a specific pricing rate/plan for a payment transaction. The list of eligible &#39;payee_pricing_tier_id&#39; would be provided to you by your Account Manager. Specifying values other than the one provided to you by your account manager would result in an error.
     *
     * @var string|null
     */
    protected $payee_pricing_tier_id;

    /**
     * FX identifier generated returned by PayPal to be used for payment processing in order to honor FX rate (for eligible integrations) to be used when amount is settled/received into the payee account.
     *
     * @var string|null
     */
    protected $payee_receivable_fx_rate_id;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->platform_fees = isset($data['platform_fees']) ? $data['platform_fees'] : null;
        $this->disbursement_mode = isset($data['disbursement_mode']) ? $data['disbursement_mode'] : null;
        $this->payee_pricing_tier_id = isset($data['payee_pricing_tier_id']) ? $data['payee_pricing_tier_id'] : null;
        $this->payee_receivable_fx_rate_id = isset($data['payee_receivable_fx_rate_id']) ? $data['payee_receivable_fx_rate_id'] : null;
    }

    /**
     * Gets platform_fees.
     *
     * @return PlatformFee[]|null
     */
    public function getPlatformFees()
    {
        return $this->platform_fees;
    }

    /**
     * Sets platform_fees.
     *
     * @param PlatformFee[]|null $platform_fees An array of various fees, commissions, tips, or donations. This field is only applicable to merchants that been enabled for PayPal Commerce Platform for Marketplaces and Platforms capability.
     *
     * @return $this
     */
    public function setPlatformFees(array $platform_fees = null)
    {
        $this->platform_fees = $platform_fees;

        return $this;
    }

    /**
     * Gets disbursement_mode.
     *
     * @return string|null
     */
    public function getDisbursementMode()
    {
        return $this->disbursement_mode;
    }

    /**
     * Sets disbursement_mode.
     *
     * @param string|null $disbursement_mode
     *
     * @return $this
     */
    public function setDisbursementMode($disbursement_mode = null)
    {
        $this->disbursement_mode = $disbursement_mode;

        return $this;
    }

    /**
     * Gets payee_pricing_tier_id.
     *
     * @return string|null
     */
    public function getPayeePricingTierId()
    {
        return $this->payee_pricing_tier_id;
    }

    /**
     * Sets payee_pricing_tier_id.
     *
     * @param string|null $payee_pricing_tier_id This field is only enabled for selected merchants/partners to use and provides the ability to trigger a specific pricing rate/plan for a payment transaction. The list of eligible 'payee_pricing_tier_id' would be provided to you by your Account Manager. Specifying values other than the one provided to you by your account manager would result in an error.
     *
     * @return $this
     */
    public function setPayeePricingTierId($payee_pricing_tier_id = null)
    {
        $this->payee_pricing_tier_id = $payee_pricing_tier_id;

        return $this;
    }

    /**
     * Gets payee_receivable_fx_rate_id.
     *
     * @return string|null
     */
    public function getPayeeReceivableFxRateId()
    {
        return $this->payee_receivable_fx_rate_id;
    }

    /**
     * Sets payee_receivable_fx_rate_id.
     *
     * @param string|null $payee_receivable_fx_rate_id FX identifier generated returned by PayPal to be used for payment processing in order to honor FX rate (for eligible integrations) to be used when amount is settled/received into the payee account
     *
     * @return $this
     */
    public function setPayeeReceivableFxRateId($payee_receivable_fx_rate_id = null)
    {
        $this->payee_receivable_fx_rate_id = $payee_receivable_fx_rate_id;

        return $this;
    }
}
