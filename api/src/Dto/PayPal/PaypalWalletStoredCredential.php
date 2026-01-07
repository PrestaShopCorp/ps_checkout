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
 * Provides additional details to process a payment using the PayPal wallet billing agreement or a
 * vaulted payment method that has been stored or is intended to be stored.
 */
class PaypalWalletStoredCredential
{
    /**
     * @var value-of<PaymentInitiator::INITIATORS>
     */
    private $paymentInitiator;

    /**
     * @var string|null
     */
    private $chargePattern;

    /**
     * @var value-of<UsagePattern::PATTERNS>|null
     */
    private $usagePattern;

    /**
     * @var value-of<StoredPaymentSourceUsageType::TYPES>|null
     */
    private $usage = StoredPaymentSourceUsageType::DERIVED;

    /**
     * @param value-of<PaymentInitiator::INITIATORS> $paymentInitiator
     */
    public function __construct(string $paymentInitiator)
    {
        $this->paymentInitiator = $paymentInitiator;
    }

    /**
     * Returns Payment Initiator.
     * The person or party who initiated or triggered the payment.
     *
     * @return value-of<PaymentInitiator::INITIATORS>
     */
    public function getPaymentInitiator(): string
    {
        return $this->paymentInitiator;
    }

    /**
     * Sets Payment Initiator.
     * The person or party who initiated or triggered the payment.
     *
     * @required
     * @maps payment_initiator
     *
     * @param value-of<PaymentInitiator::INITIATORS> $paymentInitiator
     *
     * @return self
     */
    public function setPaymentInitiator(string $paymentInitiator): self
    {
        $this->paymentInitiator = $paymentInitiator;

        return $this;
    }

    /**
     * Returns Charge Pattern.
     * DEPRECATED. Expected business/pricing model for the billing agreement, Please use usage_pattern
     * instead.
     *
     * @deprecated
     */
    public function getChargePattern(): ?string
    {
        return $this->chargePattern;
    }

    /**
     * Sets Charge Pattern.
     * DEPRECATED. Expected business/pricing model for the billing agreement, Please use usage_pattern
     * instead.
     *
     * @deprecated
     *
     * @maps charge_pattern
     * @return self
     */
    public function setChargePattern(?string $chargePattern): self
    {
        $this->chargePattern = $chargePattern;

        return $this;
    }

    /**
     * Returns Usage Pattern.
     * Expected business/pricing model for the billing agreement.
     *
     * @return value-of<UsagePattern::PATTERNS>|null
     */
    public function getUsagePattern(): ?string
    {
        return $this->usagePattern;
    }

    /**
     * Sets Usage Pattern.
     * Expected business/pricing model for the billing agreement.
     *
     * @maps usage_pattern
     *
     * @param value-of<UsagePattern::PATTERNS>|null $usagePattern
     *
     * @return self
     */
    public function setUsagePattern(?string $usagePattern): self
    {
        $this->usagePattern = $usagePattern;

        return $this;
    }

    /**
     * Returns Usage.
     * Indicates if this is a `first` or `subsequent` payment using a stored payment source (also referred
     * to as stored credential or card on file).
     *
     * @return value-of<StoredPaymentSourceUsageType::TYPES>|null
     */
    public function getUsage(): ?string
    {
        return $this->usage;
    }

    /**
     * Sets Usage.
     * Indicates if this is a `first` or `subsequent` payment using a stored payment source (also referred
     * to as stored credential or card on file).
     *
     * @maps usage
     *
     * @param value-of<StoredPaymentSourceUsageType::TYPES>|null $usage
     *
     * @return self
     */
    public function setUsage(?string $usage): self
    {
        $this->usage = $usage;

        return $this;
    }
}
