<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

/**
 * Class representing the PurchaseUnit model.
 *
 * The purchase unit details. Used to capture required information for the payment contract.
 *
 * @author  OpenAPI Generator team
 */
class PurchaseUnit
{
    /**
     * The API caller-provided external ID for the purchase unit. Required for multiple purchase units when you must update the order through &#x60;PATCH&#x60;. If you omit this value and the order contains only one purchase unit, PayPal sets this value to &#x60;default&#x60;. &lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; If there are multiple purchase units, &lt;code&gt;reference_id&lt;/code&gt; is required for each purchase unit.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $reference_id;

    /**
     * @var AmountWithBreakdown|null
     */
    protected $amount;

    /**
     * @var Payee|null
     */
    protected $payee;

    /**
     * @var PaymentInstruction|null
     */
    protected $payment_instruction;

    /**
     * The purchase description.
     *
     * @var string|null
     */
    protected $description;

    /**
     * The API caller-provided external ID. Used to reconcile API caller-initiated transactions with PayPal transactions. Appears in transaction and settlement reports.
     *
     * @var string|null
     */
    protected $custom_id;

    /**
     * The API caller-provided external invoice ID for this order.
     *
     * @var string|null
     */
    protected $invoice_id;

    /**
     * The PayPal-generated ID for the purchase unit. This ID appears in both the payer&#39;s transaction history and the emails that the payer receives. In addition, this ID is available in transaction and settlement reports that merchants and API callers can use to reconcile transactions. This ID is only available when an order is saved by calling &lt;code&gt;v2/checkout/orders/id/save&lt;/code&gt;.
     *
     * @var string|null
     */
    protected $id;

    /**
     * The payment descriptor on account transactions on the customer&#39;s credit card statement, that PayPal sends to processors. The maximum length of the soft descriptor information that you can pass in the API field is 22 characters, in the following format:&lt;code&gt;22 - len(PAYPAL * (8)) - len(&lt;var&gt;Descriptor in Payment Receiving Preferences of Merchant account&lt;/var&gt; + 1)&lt;/code&gt;The PAYPAL prefix uses 8 characters.&lt;br/&gt;&lt;br/&gt;The soft descriptor supports the following ASCII characters:&lt;ul&gt;&lt;li&gt;Alphanumeric characters&lt;/li&gt;&lt;li&gt;Dashes&lt;/li&gt;&lt;li&gt;Asterisks&lt;/li&gt;&lt;li&gt;Periods (.)&lt;/li&gt;&lt;li&gt;Spaces&lt;/li&gt;&lt;/ul&gt;For Wallet payments marketplace integrations:&lt;ul&gt;&lt;li&gt;The merchant descriptor in the Payment Receiving Preferences must be the marketplace name.&lt;/li&gt;&lt;li&gt;You can&#39;t use the remaining space to show the customer service number.&lt;/li&gt;&lt;li&gt;The remaining spaces can be a combination of seller name and country.&lt;/li&gt;&lt;/ul&gt;&lt;br/&gt;For unbranded payments (Direct Card) marketplace integrations, use a combination of the seller name and phone number.
     *
     * @var string|null
     */
    protected $soft_descriptor;

    /**
     * An array of items that the customer purchases from the merchant.
     *
     * @var Item[]|null
     */
    protected $items;

    /**
     * @var ShippingWithTrackingDetails|null
     */
    protected $shipping;

    /**
     * @var SupplementaryData|null
     */
    protected $supplementary_data;

    /**
     * @var PaymentCollection|null
     */
    protected $payments;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->reference_id = isset($data['reference_id']) ? $data['reference_id'] : null;
        $this->amount = isset($data['amount']) ? $data['amount'] : null;
        $this->payee = isset($data['payee']) ? $data['payee'] : null;
        $this->payment_instruction = isset($data['payment_instruction']) ? $data['payment_instruction'] : null;
        $this->description = isset($data['description']) ? $data['description'] : null;
        $this->custom_id = isset($data['custom_id']) ? $data['custom_id'] : null;
        $this->invoice_id = isset($data['invoice_id']) ? $data['invoice_id'] : null;
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->soft_descriptor = isset($data['soft_descriptor']) ? $data['soft_descriptor'] : null;
        $this->items = isset($data['items']) ? $data['items'] : null;
        $this->shipping = isset($data['shipping']) ? $data['shipping'] : null;
        $this->supplementary_data = isset($data['supplementary_data']) ? $data['supplementary_data'] : null;
        $this->payments = isset($data['payments']) ? $data['payments'] : null;
    }

    /**
     * Gets reference_id.
     *
     * @return string|null
     */
    public function getReferenceId()
    {
        return $this->reference_id;
    }

    /**
     * Sets reference_id.
     *
     * @param string|null $reference_id The API caller-provided external ID for the purchase unit. Required for multiple purchase units when you must update the order through `PATCH`. If you omit this value and the order contains only one purchase unit, PayPal sets this value to `default`. <blockquote><strong>Note:</strong> If there are multiple purchase units, <code>reference_id</code> is required for each purchase unit.</blockquote>
     *
     * @return $this
     */
    public function setReferenceId($reference_id = null)
    {
        $this->reference_id = $reference_id;

        return $this;
    }

    /**
     * Gets amount.
     *
     * @return AmountWithBreakdown|null
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Sets amount.
     *
     * @param AmountWithBreakdown|null $amount
     *
     * @return $this
     */
    public function setAmount(AmountWithBreakdown $amount = null)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Gets payee.
     *
     * @return Payee|null
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * Sets payee.
     *
     * @param Payee|null $payee
     *
     * @return $this
     */
    public function setPayee(Payee $payee = null)
    {
        $this->payee = $payee;

        return $this;
    }

    /**
     * Gets payment_instruction.
     *
     * @return PaymentInstruction|null
     */
    public function getPaymentInstruction()
    {
        return $this->payment_instruction;
    }

    /**
     * Sets payment_instruction.
     *
     * @param PaymentInstruction|null $payment_instruction
     *
     * @return $this
     */
    public function setPaymentInstruction(PaymentInstruction $payment_instruction = null)
    {
        $this->payment_instruction = $payment_instruction;

        return $this;
    }

    /**
     * Gets description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets description.
     *
     * @param string|null $description the purchase description
     *
     * @return $this
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

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
     * @param string|null $invoice_id the API caller-provided external invoice ID for this order
     *
     * @return $this
     */
    public function setInvoiceId($invoice_id = null)
    {
        $this->invoice_id = $invoice_id;

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
     * @param string|null $id The PayPal-generated ID for the purchase unit. This ID appears in both the payer's transaction history and the emails that the payer receives. In addition, this ID is available in transaction and settlement reports that merchants and API callers can use to reconcile transactions. This ID is only available when an order is saved by calling <code>v2/checkout/orders/id/save</code>.
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets soft_descriptor.
     *
     * @return string|null
     */
    public function getSoftDescriptor()
    {
        return $this->soft_descriptor;
    }

    /**
     * Sets soft_descriptor.
     *
     * @param string|null $soft_descriptor The payment descriptor on account transactions on the customer's credit card statement, that PayPal sends to processors. The maximum length of the soft descriptor information that you can pass in the API field is 22 characters, in the following format:<code>22 - len(PAYPAL * (8)) - len(<var>Descriptor in Payment Receiving Preferences of Merchant account</var> + 1)</code>The PAYPAL prefix uses 8 characters.<br/><br/>The soft descriptor supports the following ASCII characters:<ul><li>Alphanumeric characters</li><li>Dashes</li><li>Asterisks</li><li>Periods (.)</li><li>Spaces</li></ul>For Wallet payments marketplace integrations:<ul><li>The merchant descriptor in the Payment Receiving Preferences must be the marketplace name.</li><li>You can't use the remaining space to show the customer service number.</li><li>The remaining spaces can be a combination of seller name and country.</li></ul><br/>For unbranded payments (Direct Card) marketplace integrations, use a combination of the seller name and phone number.
     *
     * @return $this
     */
    public function setSoftDescriptor($soft_descriptor = null)
    {
        $this->soft_descriptor = $soft_descriptor;

        return $this;
    }

    /**
     * Gets items.
     *
     * @return Item[]|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets items.
     *
     * @param Item[]|null $items an array of items that the customer purchases from the merchant
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Gets shipping.
     *
     * @return ShippingWithTrackingDetails|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Sets shipping.
     *
     * @param ShippingWithTrackingDetails|null $shipping
     *
     * @return $this
     */
    public function setShipping(ShippingWithTrackingDetails $shipping = null)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Gets supplementary_data.
     *
     * @return SupplementaryData|null
     */
    public function getSupplementaryData()
    {
        return $this->supplementary_data;
    }

    /**
     * Sets supplementary_data.
     *
     * @param SupplementaryData|null $supplementary_data
     *
     * @return $this
     */
    public function setSupplementaryData(SupplementaryData $supplementary_data = null)
    {
        $this->supplementary_data = $supplementary_data;

        return $this;
    }

    /**
     * Gets payments.
     *
     * @return PaymentCollection|null
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Sets payments.
     *
     * @param PaymentCollection|null $payments
     *
     * @return $this
     */
    public function setPayments(PaymentCollection $payments = null)
    {
        $this->payments = $payments;

        return $this;
    }
}
