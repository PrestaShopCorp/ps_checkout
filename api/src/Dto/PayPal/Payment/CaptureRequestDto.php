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

use PsCheckout\Api\Dto\PayPal\CapturePaymentInstruction;
use PsCheckout\Api\Dto\PayPal\Money;

/**
 * Captures either a portion or the full authorized amount of an authorized payment.
 */
class CaptureRequestDto
{
    /**
     * @var Money|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $invoiceId;

    /**
     * @var bool|null
     */
    private $finalCapture = false;

    /**
     * @var CapturePaymentInstruction|null
     */
    private $paymentInstruction;

    /**
     * @var string|null
     */
    private $noteToPayer;

    /**
     * @var string|null
     */
    private $softDescriptor;

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
     * Returns Invoice Id.
     * The API caller-provided external invoice number for this order. Appears in both the payer's
     * transaction history and the emails that the payer receives.
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * Sets Invoice Id.
     * The API caller-provided external invoice number for this order. Appears in both the payer's
     * transaction history and the emails that the payer receives.
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
     * Returns Final Capture.
     * Indicates whether you can make additional captures against the authorized payment. Set to `true` if
     * you do not intend to capture additional payments against the authorization. Set to `false` if you
     * intend to capture additional payments against the authorization.
     */
    public function getFinalCapture(): ?bool
    {
        return $this->finalCapture;
    }

    /**
     * Sets Final Capture.
     * Indicates whether you can make additional captures against the authorized payment. Set to `true` if
     * you do not intend to capture additional payments against the authorization. Set to `false` if you
     * intend to capture additional payments against the authorization.
     *
     * @maps final_capture
     * @return self
     */
    public function setFinalCapture(?bool $finalCapture): self
    {
        $this->finalCapture = $finalCapture;

        return $this;
    }

    /**
     * Returns Payment Instruction.
     * Any additional payment instructions to be consider during payment processing. This processing
     * instruction is applicable for Capturing an order or Authorizing an Order.
     */
    public function getPaymentInstruction(): ?CapturePaymentInstruction
    {
        return $this->paymentInstruction;
    }

    /**
     * Sets Payment Instruction.
     * Any additional payment instructions to be consider during payment processing. This processing
     * instruction is applicable for Capturing an order or Authorizing an Order.
     *
     * @maps payment_instruction
     * @return self
     */
    public function setPaymentInstruction(?CapturePaymentInstruction $paymentInstruction): self
    {
        $this->paymentInstruction = $paymentInstruction;

        return $this;
    }

    /**
     * Returns Note to Payer.
     * An informational note about this settlement. Appears in both the payer's transaction history and the
     * emails that the payer receives.
     */
    public function getNoteToPayer(): ?string
    {
        return $this->noteToPayer;
    }

    /**
     * Sets Note to Payer.
     * An informational note about this settlement. Appears in both the payer's transaction history and the
     * emails that the payer receives.
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
     * Returns Soft Descriptor.
     * The payment descriptor on the payer's account statement.
     */
    public function getSoftDescriptor(): ?string
    {
        return $this->softDescriptor;
    }

    /**
     * Sets Soft Descriptor.
     * The payment descriptor on the payer's account statement.
     *
     * @maps soft_descriptor
     * @return self
     */
    public function setSoftDescriptor(?string $softDescriptor): self
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }
}
