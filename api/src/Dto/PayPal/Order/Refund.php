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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\PayeeBase;

/**
 * The refund information.
 */
class Refund
{
    /**
     * @var string|null
     */
    private $status;

    /**
     * @var RefundStatusDetails|null
     */
    private $statusDetails;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var Money|null
     */
    private $amount;

    /**
     * @var string|null
     */
    private $invoiceId;

    /**
     * @var string|null
     */
    private $customId;

    /**
     * @var string|null
     */
    private $acquirerReferenceNumber;

    /**
     * @var string|null
     */
    private $noteToPayer;

    /**
     * @var SellerPayableBreakdown|null
     */
    private $sellerPayableBreakdown;

    /**
     * @var PayeeBase|null
     */
    private $payer;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var string|null
     */
    private $createTime;

    /**
     * @var string|null
     */
    private $updateTime;

    /**
     * Returns Status.
     * The status of the refund.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status of the refund.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Status Details.
     * The details of the refund status.
     */
    public function getStatusDetails(): ?RefundStatusDetails
    {
        return $this->statusDetails;
    }

    /**
     * Sets Status Details.
     * The details of the refund status.
     *
     * @maps status_details
     */
    public function setStatusDetails(?RefundStatusDetails $statusDetails): void
    {
        $this->statusDetails = $statusDetails;
    }

    /**
     * Returns Id.
     * The PayPal-generated ID for the refund.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the refund.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

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
     */
    public function setAmount(?Money $amount): void
    {
        $this->amount = $amount;
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
     */
    public function setInvoiceId(?string $invoiceId): void
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Returns Custom Id.
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal
     * transactions. Appears in transaction and settlement reports.
     */
    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    /**
     * Sets Custom Id.
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal
     * transactions. Appears in transaction and settlement reports.
     *
     * @maps custom_id
     */
    public function setCustomId(?string $customId): void
    {
        $this->customId = $customId;
    }

    /**
     * Returns Acquirer Reference Number.
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across
     * processors, card brands and issuing banks.
     */
    public function getAcquirerReferenceNumber(): ?string
    {
        return $this->acquirerReferenceNumber;
    }

    /**
     * Sets Acquirer Reference Number.
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across
     * processors, card brands and issuing banks.
     *
     * @maps acquirer_reference_number
     */
    public function setAcquirerReferenceNumber(?string $acquirerReferenceNumber): void
    {
        $this->acquirerReferenceNumber = $acquirerReferenceNumber;
    }

    /**
     * Returns Note to Payer.
     * The reason for the refund. Appears in both the payer's transaction history and the emails that the
     * payer receives.
     */
    public function getNoteToPayer(): ?string
    {
        return $this->noteToPayer;
    }

    /**
     * Sets Note to Payer.
     * The reason for the refund. Appears in both the payer's transaction history and the emails that the
     * payer receives.
     *
     * @maps note_to_payer
     */
    public function setNoteToPayer(?string $noteToPayer): void
    {
        $this->noteToPayer = $noteToPayer;
    }

    /**
     * Returns Seller Payable Breakdown.
     * The breakdown of the refund.
     */
    public function getSellerPayableBreakdown(): ?SellerPayableBreakdown
    {
        return $this->sellerPayableBreakdown;
    }

    /**
     * Sets Seller Payable Breakdown.
     * The breakdown of the refund.
     *
     * @maps seller_payable_breakdown
     */
    public function setSellerPayableBreakdown(?SellerPayableBreakdown $sellerPayableBreakdown): void
    {
        $this->sellerPayableBreakdown = $sellerPayableBreakdown;
    }

    /**
     * Returns Payer.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     */
    public function getPayer(): ?PayeeBase
    {
        return $this->payer;
    }

    /**
     * Sets Payer.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     *
     * @maps payer
     */
    public function setPayer(?PayeeBase $payer): void
    {
        $this->payer = $payer;
    }

    /**
     * Returns Links.
     * An array of related [HATEOAS links](/docs/api/reference/api-responses/#hateoas-links).
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Sets Links.
     * An array of related [HATEOAS links](/docs/api/reference/api-responses/#hateoas-links).
     *
     * @maps links
     *
     * @param LinkDescription[]|null $links
     */
    public function setLinks(?array $links): void
    {
        $this->links = $links;
    }

    /**
     * Returns Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getCreateTime(): ?string
    {
        return $this->createTime;
    }

    /**
     * Sets Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps create_time
     */
    public function setCreateTime(?string $createTime): void
    {
        $this->createTime = $createTime;
    }

    /**
     * Returns Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getUpdateTime(): ?string
    {
        return $this->updateTime;
    }

    /**
     * Sets Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps update_time
     */
    public function setUpdateTime(?string $updateTime): void
    {
        $this->updateTime = $updateTime;
    }
}
