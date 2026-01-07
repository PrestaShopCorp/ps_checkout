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

namespace PsCheckout\Api\Dto\PayPal\Payment;

use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\RefundPaymentInstruction;

/**
 * Refunds a captured payment, by ID. For a full refund, include an empty request body. For a partial
 * refund, include an amount object in the request body.
 */
class RefundRequestDto
{
    /**
     * @var Money|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $customId;

    /**
     * @var string|null
     */
    private $invoiceId;

    /**
     * @var string|null
     */
    private $noteToPayer;

    /**
     * @var RefundPaymentInstruction|null
     */
    private $paymentInstruction;

    /**
     * Returns Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     */
    public function getAmount(): ?Money
    {
        return $this->amount;
    }

    /**
     * Sets Amount.
     * The currency and amount for a financial transaction, such as a balance or payment due.
     *
     * @maps amount
     * @return self
     */
    public function setAmount(?Money $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Returns Custom Id.
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal
     * transactions. Appears in transaction and settlement reports. The pattern is defined by an external
     * party and supports Unicode.
     */
    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    /**
     * Sets Custom Id.
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal
     * transactions. Appears in transaction and settlement reports. The pattern is defined by an external
     * party and supports Unicode.
     *
     * @maps custom_id
     * @return self
     */
    public function setCustomId(?string $customId): self
    {
        $this->customId = $customId;

        return $this;
    }

    /**
     * Returns Invoice Id.
     * The API caller-provided external invoice ID for this order. The pattern is defined by an external
     * party and supports Unicode.
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * Sets Invoice Id.
     * The API caller-provided external invoice ID for this order. The pattern is defined by an external
     * party and supports Unicode.
     *
     * @maps invoice_id
     * @return self
     */
    public function setInvoiceId(?string $invoiceId): self
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * Returns Note to Payer.
     * The reason for the refund. Appears in both the payer's transaction history and the emails that the
     * payer receives. The pattern is defined by an external party and supports Unicode.
     */
    public function getNoteToPayer(): ?string
    {
        return $this->noteToPayer;
    }

    /**
     * Sets Note to Payer.
     * The reason for the refund. Appears in both the payer's transaction history and the emails that the
     * payer receives. The pattern is defined by an external party and supports Unicode.
     *
     * @maps note_to_payer
     * @return self
     */
    public function setNoteToPayer(?string $noteToPayer): self
    {
        $this->noteToPayer = $noteToPayer;

        return $this;
    }

    /**
     * Returns Payment Instruction.
     * Any additional payments instructions during refund payment processing. This object is only
     * applicable to merchants that have been enabled for PayPal Commerce Platform for Marketplaces and
     * Platforms capability. Please speak to your account manager if you want to use this capability.
     */
    public function getPaymentInstruction(): ?RefundPaymentInstruction
    {
        return $this->paymentInstruction;
    }

    /**
     * Sets Payment Instruction.
     * Any additional payments instructions during refund payment processing. This object is only
     * applicable to merchants that have been enabled for PayPal Commerce Platform for Marketplaces and
     * Platforms capability. Please speak to your account manager if you want to use this capability.
     *
     * @maps payment_instruction
     * @return self
     */
    public function setPaymentInstruction(?RefundPaymentInstruction $paymentInstruction): self
    {
        $this->paymentInstruction = $paymentInstruction;

        return $this;
    }
}
