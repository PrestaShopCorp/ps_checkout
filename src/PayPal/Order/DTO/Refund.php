<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Refund
{
    /**
     * The status of the refund.
     *
     * @var string|null
     */
    protected $status;

    /**
     * @var Reason|null
     */
    protected $status_details;

    /**
     * The PayPal-generated ID for the refund.
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
     * Reference ID issued for the card transaction. This ID can be used to track the transaction across processors, card brands and issuing banks.
     *
     * @var string|null
     */
    protected $acquirer_reference_number;

    /**
     * The reason for the refund. Appears in both the payer&#39;s transaction history and the emails that the payer receives.
     *
     * @var string|null
     */
    protected $note_to_payer;

    /**
     * @var MerchantPayableBreakdown|null
     */
    protected $seller_payable_breakdown;

    /**
     * @var Payee|null
     */
    protected $payer;

    /**
     * An array of related [HATEOAS links](/docs/api/reference/api-responses/#hateoas-links).
     *
     * @var LinkDescription[]|null
     */
    protected $links;

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
        $this->acquirer_reference_number = isset($data['acquirer_reference_number']) ? $data['acquirer_reference_number'] : null;
        $this->note_to_payer = isset($data['note_to_payer']) ? $data['note_to_payer'] : null;
        $this->seller_payable_breakdown = isset($data['seller_payable_breakdown']) ? $data['seller_payable_breakdown'] : null;
        $this->payer = isset($data['payer']) ? $data['payer'] : null;
        $this->links = isset($data['links']) ? $data['links'] : null;
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
     * @param string|null $status the status of the refund
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
     * @return RefundStatusDetails|null
     */
    public function getStatusDetails(): ?RefundStatusDetails
    {
        return $this->status_details;
    }

    /**
     * Sets status_details.
     *
     * @param RefundStatusDetails|null $status_details
     *
     * @return $this
     */
    public function setStatusDetails(RefundStatusDetails $status_details = null)
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
     * @param string|null $id the PayPal-generated ID for the refund
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
     * Gets acquirer_reference_number.
     *
     * @return string|null
     */
    public function getAcquirerReferenceNumber()
    {
        return $this->acquirer_reference_number;
    }

    /**
     * Sets acquirer_reference_number.
     *
     * @param string|null $acquirer_reference_number Reference ID issued for the card transaction. This ID can be used to track the transaction across processors, card brands and issuing banks.
     *
     * @return $this
     */
    public function setAcquirerReferenceNumber($acquirer_reference_number = null)
    {
        $this->acquirer_reference_number = $acquirer_reference_number;

        return $this;
    }

    /**
     * Gets note_to_payer.
     *
     * @return string|null
     */
    public function getNoteToPayer()
    {
        return $this->note_to_payer;
    }

    /**
     * Sets note_to_payer.
     *
     * @param string|null $note_to_payer The reason for the refund. Appears in both the payer's transaction history and the emails that the payer receives.
     *
     * @return $this
     */
    public function setNoteToPayer($note_to_payer = null)
    {
        $this->note_to_payer = $note_to_payer;

        return $this;
    }

    /**
     * Gets seller_payable_breakdown.
     *
     * @return MerchantPayableBreakdown|null
     */
    public function getSellerPayableBreakdown(): ?MerchantPayableBreakdown
    {
        return $this->seller_payable_breakdown;
    }

    /**
     * Sets seller_payable_breakdown.
     *
     * @param MerchantPayableBreakdown|null $seller_payable_breakdown
     *
     * @return $this
     */
    public function setSellerPayableBreakdown(MerchantPayableBreakdown $seller_payable_breakdown = null)
    {
        $this->seller_payable_breakdown = $seller_payable_breakdown;

        return $this;
    }

    /**
     * Gets payer.
     *
     * @return PayeeBase|null
     */
    public function getPayer(): ?PayeeBase
    {
        return $this->payer;
    }

    /**
     * Sets payer.
     *
     * @param PayeeBase|null $payer
     *
     * @return $this
     */
    public function setPayer(PayeeBase $payer = null)
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * Gets links.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
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
