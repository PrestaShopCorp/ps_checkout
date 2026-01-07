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

use PaypalServerSdkLib\Models\SellerReceivableBreakdown;
use PsCheckout\Api\Dto\PayPal\DisbursementMode;
use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\NetworkTransaction;
use PsCheckout\Api\Dto\PayPal\SellerProtection;

/**
 * A captured payment.
 */
class OrdersCapture
{
    /**
     * @var string|null
     */
    private $status;

    /**
     * @var CaptureStatusDetails|null
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
     * @var NetworkTransaction|null
     */
    private $networkTransactionReference;

    /**
     * @var SellerProtection|null
     */
    private $sellerProtection;

    /**
     * @var bool|null
     */
    private $finalCapture = false;

    /**
     * @var SellerReceivableBreakdown|null
     */
    private $sellerReceivableBreakdown;

    /**
     * @var string|null
     */
    private $disbursementMode = DisbursementMode::INSTANT;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var ProcessorResponse|null
     */
    private $processorResponse;

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
     * The status of the captured payment.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status of the captured payment.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Status Details.
     * The details of the captured payment status.
     */
    public function getStatusDetails(): ?CaptureStatusDetails
    {
        return $this->statusDetails;
    }

    /**
     * Sets Status Details.
     * The details of the captured payment status.
     *
     * @maps status_details
     */
    public function setStatusDetails(?CaptureStatusDetails $statusDetails): void
    {
        $this->statusDetails = $statusDetails;
    }

    /**
     * Returns Id.
     * The PayPal-generated ID for the captured payment.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the captured payment.
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
     * Returns Network Transaction Reference.
     * Reference values used by the card network to identify a transaction.
     */
    public function getNetworkTransactionReference(): ?NetworkTransaction
    {
        return $this->networkTransactionReference;
    }

    /**
     * Sets Network Transaction Reference.
     * Reference values used by the card network to identify a transaction.
     *
     * @maps network_transaction_reference
     */
    public function setNetworkTransactionReference(?NetworkTransaction $networkTransactionReference): void
    {
        $this->networkTransactionReference = $networkTransactionReference;
    }

    /**
     * Returns Seller Protection.
     * The level of protection offered as defined by [PayPal Seller Protection for Merchants](https://www.
     * paypal.com/us/webapps/mpp/security/seller-protection).
     */
    public function getSellerProtection(): ?SellerProtection
    {
        return $this->sellerProtection;
    }

    /**
     * Sets Seller Protection.
     * The level of protection offered as defined by [PayPal Seller Protection for Merchants](https://www.
     * paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @maps seller_protection
     */
    public function setSellerProtection(?SellerProtection $sellerProtection): void
    {
        $this->sellerProtection = $sellerProtection;
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
     */
    public function setFinalCapture(?bool $finalCapture): void
    {
        $this->finalCapture = $finalCapture;
    }

    /**
     * Returns Seller Receivable Breakdown.
     * The detailed breakdown of the capture activity. This is not available for transactions that are in
     * pending state.
     */
    public function getSellerReceivableBreakdown(): ?SellerReceivableBreakdown
    {
        return $this->sellerReceivableBreakdown;
    }

    /**
     * Sets Seller Receivable Breakdown.
     * The detailed breakdown of the capture activity. This is not available for transactions that are in
     * pending state.
     *
     * @maps seller_receivable_breakdown
     */
    public function setSellerReceivableBreakdown(?SellerReceivableBreakdown $sellerReceivableBreakdown): void
    {
        $this->sellerReceivableBreakdown = $sellerReceivableBreakdown;
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
     * Returns Processor Response.
     * The processor response information for payment requests, such as direct credit card transactions.
     */
    public function getProcessorResponse(): ?ProcessorResponse
    {
        return $this->processorResponse;
    }

    /**
     * Sets Processor Response.
     * The processor response information for payment requests, such as direct credit card transactions.
     *
     * @maps processor_response
     */
    public function setProcessorResponse(?ProcessorResponse $processorResponse): void
    {
        $this->processorResponse = $processorResponse;
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
