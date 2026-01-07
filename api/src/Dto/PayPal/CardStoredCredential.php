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
 * Provides additional details to process a payment using a `card` that has been stored or is intended
 * to be stored (also referred to as stored_credential or card-on-file). Parameter compatibility:
 * `payment_type=ONE_TIME` is compatible only with `payment_initiator=CUSTOMER`. `usage=FIRST` is
 * compatible only with `payment_initiator=CUSTOMER`. `previous_transaction_reference` or
 * `previous_network_transaction_reference` is compatible only with `payment_initiator=MERCHANT`. Only
 * one of the parameters - `previous_transaction_reference` and
 * `previous_network_transaction_reference` - can be present in the request.
 */
class CardStoredCredential
{
    /**
     * @var string
     */
    private $paymentInitiator;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string|null
     */
    private $usage = StoredPaymentSourceUsageType::DERIVED;

    /**
     * @var NetworkTransaction|null
     */
    private $previousNetworkTransactionReference;

    /**
     * @param string $paymentInitiator
     * @param string $paymentType
     */
    public function __construct(string $paymentInitiator, string $paymentType)
    {
        $this->paymentInitiator = $paymentInitiator;
        $this->paymentType = $paymentType;
    }

    /**
     * Returns Payment Initiator.
     * The person or party who initiated or triggered the payment.
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
     */
    public function setPaymentInitiator(string $paymentInitiator): void
    {
        $this->paymentInitiator = $paymentInitiator;
    }

    /**
     * Returns Payment Type.
     * Indicates the type of the stored payment_source payment.
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * Sets Payment Type.
     * Indicates the type of the stored payment_source payment.
     *
     * @required
     * @maps payment_type
     */
    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }

    /**
     * Returns Usage.
     * Indicates if this is a `first` or `subsequent` payment using a stored payment source (also referred
     * to as stored credential or card on file).
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
     */
    public function setUsage(?string $usage): void
    {
        $this->usage = $usage;
    }

    /**
     * Returns Previous Network Transaction Reference.
     * Reference values used by the card network to identify a transaction.
     */
    public function getPreviousNetworkTransactionReference(): ?NetworkTransaction
    {
        return $this->previousNetworkTransactionReference;
    }

    /**
     * Sets Previous Network Transaction Reference.
     * Reference values used by the card network to identify a transaction.
     *
     * @maps previous_network_transaction_reference
     */
    public function setPreviousNetworkTransactionReference(
        ?NetworkTransaction $previousNetworkTransactionReference
    ): void {
        $this->previousNetworkTransactionReference = $previousNetworkTransactionReference;
    }
}
