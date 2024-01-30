<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Level2CardProcessingData
{
    /**
     * Use this field to pass a purchase identification value of up to 12 ASCII characters for AIB and 17 ASCII characters for all other processors.
     *
     * @var string|null
     */
    protected $invoice_id;
    /**
     * @var Amount|null
     */
    protected $tax_total;
    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->invoice_id = isset($data['invoice_id']) ? $data['invoice_id'] : null;
        $this->tax_total = isset($data['tax_total']) ? $data['tax_total'] : null;
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
     * @param string|null $invoice_id  Use this field to pass a purchase identification value of up to 12 ASCII characters for AIB and 17 ASCII characters for all other processors.
     *
     * @return $this
     */
    public function setInvoiceId($invoice_id = null)
    {
        $this->invoice_id = $invoice_id;
        return $this;
    }
    /**
     * Gets tax_total.
     *
     * @return Amount|null
     */
    public function getTaxTotal()
    {
        return $this->tax_total;
    }
    /**
     * Sets tax_total.
     *
     * @param Amount|null $tax_total
     *
     * @return $this
     */
    public function setTaxTotal(Amount $tax_total = null)
    {
        $this->tax_total = $tax_total;
        return $this;
    }
}
