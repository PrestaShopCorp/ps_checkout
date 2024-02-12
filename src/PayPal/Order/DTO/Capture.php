<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Capture
{
    /**
     * The status of the captured payment.
     *
     * @var string|null
     */
    protected $status;

    /**
     * @var Reason|null
     */
    protected $status_details;

    /**
     * The PayPal-generated ID for the captured payment.
     *
     * @var string|null
     */
    protected $id;

    /**
     * @var Amount|null
     */
    protected $amount;

    /**
     * The API caller-provided external invoice number for this order. Appears in both the payer&#39;s transaction history and the emails that the payer receives.
     *
     * @var string|null
     */
    protected $invoice_id;

    /**
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal transactions. Appears in transaction and settlement reports.
     *
     * @var string|null
     */
    protected $custom_id;

    /**
     * @var NetworkTransactionReference|null
     */
    protected $network_transaction_reference;

    /**
     * @var SellerProtection|null
     */
    protected $seller_protection;

    /**
     * Indicates whether you can make additional captures against the authorized payment. Set to &#x60;true&#x60; if you do not intend to capture additional payments against the authorization. Set to &#x60;false&#x60; if you intend to capture additional payments against the authorization.
     *
     * @var bool|null
     */
    protected $final_capture;

    /**
     * @var SellerReceivableBreakdown|null
     */
    protected $seller_receivable_breakdown;

    /**
     * @var string|null
     */
    protected $disbursement_mode;

    /**
     * An array of related [HATEOAS links](/docs/api/reference/api-responses/#hateoas-links).
     *
     * @var LinkDescription[]|null
     */
    protected $links;

    /**
     * @var ProcessorResponse|null
     */
    protected $processor_response;

    /**
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The regular expression provides guidance but does not reject all invalid dates.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $create_time;

    /**
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The regular expression provides guidance but does not reject all invalid dates.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $update_time;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->status = isset($data['status']) ? $data['status'] : null;
        $this->status_details = isset($data['status_details']) ? $data['status_details'] : null;
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
        $this->invoice_id = isset($data['invoice_id']) ? $data['invoice_id'] : null;
        $this->custom_id = isset($data['custom_id']) ? $data['custom_id'] : null;
        $this->network_transaction_reference = isset($data['network_transaction_reference']) ? $data['network_transaction_reference'] : null;
        $this->seller_protection = isset($data['seller_protection']) ? $data['seller_protection'] : null;
        $this->final_capture = isset($data['final_capture']) ? $data['final_capture'] : false;
        $this->seller_receivable_breakdown = isset($data['seller_receivable_breakdown']) ? $data['seller_receivable_breakdown'] : null;
        $this->disbursement_mode = isset($data['disbursement_mode']) ? $data['disbursement_mode'] : null;
        $this->links = isset($data['links']) ? $data['links'] : null;
        $this->processor_response = isset($data['processor_response']) ? $data['processor_response'] : null;
        $this->create_time = isset($data['create_time']) ? $data['create_time'] : null;
        $this->update_time = isset($data['update_time']) ? $data['update_time'] : null;
    }

    /**
     * Gets status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status.
     *
     * @param string|null $status the status of the captured payment
     *
     * @return $this
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets status_details.
     *
     * @return Reason|null
     */
    public function getStatusDetails()
    {
        return $this->status_details;
    }

    /**
     * Sets status_details.
     *
     * @param Reason|null $status_details
     *
     * @return $this
     */
    public function setStatusDetails(Reason $status_details = null)
    {
        $this->status_details = $status_details;

        return $this;
    }

    /**
     * Gets id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param string|null $id the PayPal-generated ID for the captured payment
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets amount.
     *
     * @return Amount|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets amount.
     *
     * @param Amount|null $amount
     *
     * @return $this
     */
    public function setAmount(Amount $amount = null)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets invoice_id.
     *
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoice_id;
    }

    /**
     * Sets invoice_id.
     *
     * @param string|null $invoice_id The API caller-provided external invoice number for this order. Appears in both the payer's transaction history and the emails that the payer receives.
     *
     * @return $this
     */
    public function setInvoiceId($invoice_id = null)
    {
        $this->invoice_id = $invoice_id;

        return $this;
    }

    /**
     * Gets custom_id.
     *
     * @return string|null
     */
    public function getCustomId()
    {
        return $this->custom_id;
    }

    /**
     * Sets custom_id.
     *
     * @param string|null $custom_id The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal transactions. Appears in transaction and settlement reports.
     *
     * @return $this
     */
    public function setCustomId($custom_id = null)
    {
        $this->custom_id = $custom_id;

        return $this;
    }

    /**
     * Gets network_transaction_reference.
     *
     * @return NetworkTransactionReference|null
     */
    public function getNetworkTransactionReference()
    {
        return $this->network_transaction_reference;
    }

    /**
     * Sets network_transaction_reference.
     *
     * @param NetworkTransactionReference|null $network_transaction_reference
     *
     * @return $this
     */
    public function setNetworkTransactionReference(NetworkTransactionReference $network_transaction_reference = null)
    {
        $this->network_transaction_reference = $network_transaction_reference;

        return $this;
    }

    /**
     * Gets seller_protection.
     *
     * @return SellerProtection|null
     */
    public function getSellerProtection()
    {
        return $this->seller_protection;
    }

    /**
     * Sets seller_protection.
     *
     * @param SellerProtection|null $seller_protection
     *
     * @return $this
     */
    public function setSellerProtection(SellerProtection $seller_protection = null)
    {
        $this->seller_protection = $seller_protection;

        return $this;
    }

    /**
     * Gets final_capture.
     *
     * @return bool|null
     */
    public function isFinalCapture()
    {
        return $this->final_capture;
    }

    /**
     * Sets final_capture.
     *
     * @param bool|null $final_capture Indicates whether you can make additional captures against the authorized payment. Set to `true` if you do not intend to capture additional payments against the authorization. Set to `false` if you intend to capture additional payments against the authorization.
     *
     * @return $this
     */
    public function setFinalCapture($final_capture = null)
    {
        $this->final_capture = $final_capture;

        return $this;
    }

    /**
     * Gets seller_receivable_breakdown.
     *
     * @return SellerReceivableBreakdown|null
     */
    public function getSellerReceivableBreakdown()
    {
        return $this->seller_receivable_breakdown;
    }

    /**
     * Sets seller_receivable_breakdown.
     *
     * @param SellerReceivableBreakdown|null $seller_receivable_breakdown
     *
     * @return $this
     */
    public function setSellerReceivableBreakdown(SellerReceivableBreakdown $seller_receivable_breakdown = null)
    {
        $this->seller_receivable_breakdown = $seller_receivable_breakdown;

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
     * Gets links.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Sets links.
     *
     * @param LinkDescription[]|null $links an array of related [HATEOAS links](/docs/api/reference/api-responses/#hateoas-links)
     *
     * @return $this
     */
    public function setLinks(array $links = null)
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Gets processor_response.
     *
     * @return ProcessorResponse|null
     */
    public function getProcessorResponse()
    {
        return $this->processor_response;
    }

    /**
     * Sets processor_response.
     *
     * @param ProcessorResponse|null $processor_response
     *
     * @return $this
     */
    public function setProcessorResponse(ProcessorResponse $processor_response = null)
    {
        $this->processor_response = $processor_response;

        return $this;
    }

    /**
     * Gets create_time.
     *
     * @return string|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Sets create_time.
     *
     * @param string|null $create_time The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.<blockquote><strong>Note:</strong> The regular expression provides guidance but does not reject all invalid dates.</blockquote>
     *
     * @return $this
     */
    public function setCreateTime($create_time = null)
    {
        $this->create_time = $create_time;

        return $this;
    }

    /**
     * Gets update_time.
     *
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Sets update_time.
     *
     * @param string|null $update_time The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.<blockquote><strong>Note:</strong> The regular expression provides guidance but does not reject all invalid dates.</blockquote>
     *
     * @return $this
     */
    public function setUpdateTime($update_time = null)
    {
        $this->update_time = $update_time;

        return $this;
    }
}
