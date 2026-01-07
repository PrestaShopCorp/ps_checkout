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
 * The purchase unit details. Used to capture required information for the payment contract.
 */
class PurchaseUnit
{
    /**
     * @var string|null
     */
    private $referenceId;

    /**
     * @var AmountWithBreakdown|null
     */
    private $amount;

    /**
     * @var PayeeBase|null
     */
    private $payee;

    /**
     * @var PaymentInstruction|null
     */
    private $paymentInstruction;

    /**
     * @var string|null
     */
    private $description;

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
    private $id;

    /**
     * @var string|null
     */
    private $softDescriptor;

    /**
     * @var Item[]|null
     */
    private $items;

    /**
     * @var ShippingWithTrackingDetails|null
     */
    private $shipping;

    /**
     * @var SupplementaryData|null
     */
    private $supplementaryData;

    /**
     * @var PaymentCollection|null
     */
    private $payments;

    /**
     * @var array<mixed>|null
     */
    private $mostRecentErrors;

    /**
     * Returns Reference Id.
     * The API caller-provided external ID for the purchase unit. Required for multiple purchase units when
     * you must update the order through `PATCH`. If you omit this value and the order contains only one
     * purchase unit, PayPal sets this value to `default`. Note: If there are multiple purchase units,
     * reference_id is required for each purchase unit.
     */
    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    /**
     * Sets Reference Id.
     * The API caller-provided external ID for the purchase unit. Required for multiple purchase units when
     * you must update the order through `PATCH`. If you omit this value and the order contains only one
     * purchase unit, PayPal sets this value to `default`. Note: If there are multiple purchase units,
     * reference_id is required for each purchase unit.
     *
     * @maps reference_id
     * @return self
     */
    public function setReferenceId(?string $referenceId): self
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Returns Amount.
     * The total order amount with an optional breakdown that provides details, such as the total item
     * amount, total tax amount, shipping, handling, insurance, and discounts, if any. If you specify
     * `amount.breakdown`, the amount equals `item_total` plus `tax_total` plus `shipping` plus `handling`
     * plus `insurance` minus `shipping_discount` minus discount. The amount must be a positive number. For
     * listed of supported currencies and decimal precision, see the PayPal REST APIs Currency Codes.
     */
    public function getAmount(): ?AmountWithBreakdown
    {
        return $this->amount;
    }

    /**
     * Sets Amount.
     * The total order amount with an optional breakdown that provides details, such as the total item
     * amount, total tax amount, shipping, handling, insurance, and discounts, if any. If you specify
     * `amount.breakdown`, the amount equals `item_total` plus `tax_total` plus `shipping` plus `handling`
     * plus `insurance` minus `shipping_discount` minus discount. The amount must be a positive number. For
     * listed of supported currencies and decimal precision, see the PayPal REST APIs Currency Codes.
     *
     * @maps amount
     * @return self
     */
    public function setAmount(?AmountWithBreakdown $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Returns Payee.
     * The merchant who receives the funds and fulfills the order. The merchant is also known as the payee.
     */
    public function getPayee(): ?PayeeBase
    {
        return $this->payee;
    }

    /**
     * Sets Payee.
     * The merchant who receives the funds and fulfills the order. The merchant is also known as the payee.
     *
     * @maps payee
     * @return self
     */
    public function setPayee(?PayeeBase $payee): self
    {
        $this->payee = $payee;

        return $this;
    }

    /**
     * Returns Payment Instruction.
     * Any additional payment instructions to be consider during payment processing. This processing
     * instruction is applicable for Capturing an order or Authorizing an Order.
     */
    public function getPaymentInstruction(): ?PaymentInstruction
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
    public function setPaymentInstruction(?PaymentInstruction $paymentInstruction): self
    {
        $this->paymentInstruction = $paymentInstruction;

        return $this;
    }

    /**
     * Returns Description.
     * The purchase description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * The purchase description.
     *
     * @maps description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
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
     * @return self
     */
    public function setCustomId(?string $customId): self
    {
        $this->customId = $customId;

        return $this;
    }

    /**
     * Returns Invoice Id.
     * The API caller-provided external invoice ID for this order.
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * Sets Invoice Id.
     * The API caller-provided external invoice ID for this order.
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
     * Returns Id.
     * The PayPal-generated ID for the purchase unit. This ID appears in both the payer's transaction
     * history and the emails that the payer receives. In addition, this ID is available in transaction and
     * settlement reports that merchants and API callers can use to reconcile transactions. This ID is only
     * available when an order is saved by calling v2/checkout/orders/id/save.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the purchase unit. This ID appears in both the payer's transaction
     * history and the emails that the payer receives. In addition, this ID is available in transaction and
     * settlement reports that merchants and API callers can use to reconcile transactions. This ID is only
     * available when an order is saved by calling v2/checkout/orders/id/save.
     *
     * @maps id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns Soft Descriptor.
     * The payment descriptor on account transactions on the customer's credit card statement, that PayPal
     * sends to processors. The maximum length of the soft descriptor information that you can pass in the
     * API field is 22 characters, in the following format:22 - len(PAYPAL * (8)) - len(Descriptor in
     * Payment Receiving Preferences of Merchant account + 1)The PAYPAL prefix uses 8 characters. The soft
     * descriptor supports the following ASCII characters: Alphanumeric characters Dashes Asterisks Periods
     * (.) Spaces For Wallet payments marketplace integrations: The merchant descriptor in the Payment
     * Receiving Preferences must be the marketplace name. You can't use the remaining space to show the
     * customer service number. The remaining spaces can be a combination of seller name and country. For
     * unbranded payments (Direct Card) marketplace integrations, use a combination of the seller name and
     * phone number.
     */
    public function getSoftDescriptor(): ?string
    {
        return $this->softDescriptor;
    }

    /**
     * Sets Soft Descriptor.
     * The payment descriptor on account transactions on the customer's credit card statement, that PayPal
     * sends to processors. The maximum length of the soft descriptor information that you can pass in the
     * API field is 22 characters, in the following format:22 - len(PAYPAL * (8)) - len(Descriptor in
     * Payment Receiving Preferences of Merchant account + 1)The PAYPAL prefix uses 8 characters. The soft
     * descriptor supports the following ASCII characters: Alphanumeric characters Dashes Asterisks Periods
     * (.) Spaces For Wallet payments marketplace integrations: The merchant descriptor in the Payment
     * Receiving Preferences must be the marketplace name. You can't use the remaining space to show the
     * customer service number. The remaining spaces can be a combination of seller name and country. For
     * unbranded payments (Direct Card) marketplace integrations, use a combination of the seller name and
     * phone number.
     *
     * @maps soft_descriptor
     * @return self
     */
    public function setSoftDescriptor(?string $softDescriptor): self
    {
        $this->softDescriptor = $softDescriptor;

        return $this;
    }

    /**
     * Returns Items.
     * An array of items that the customer purchases from the merchant.
     *
     * @return Item[]|null
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Sets Items.
     * An array of items that the customer purchases from the merchant.
     *
     * @maps items
     *
     * @param Item[]|null $items
     * @return self
     */
    public function setItems(?array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Returns Shipping.
     * The order shipping details.
     */
    public function getShipping(): ?ShippingWithTrackingDetails
    {
        return $this->shipping;
    }

    /**
     * Sets Shipping.
     * The order shipping details.
     *
     * @maps shipping
     * @return self
     */
    public function setShipping(?ShippingWithTrackingDetails $shipping): self
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Returns Supplementary Data.
     * Supplementary data about a payment. This object passes information that can be used to improve risk
     * assessments and processing costs, for example, by providing Level 2 and Level 3 payment data.
     */
    public function getSupplementaryData(): ?SupplementaryData
    {
        return $this->supplementaryData;
    }

    /**
     * Sets Supplementary Data.
     * Supplementary data about a payment. This object passes information that can be used to improve risk
     * assessments and processing costs, for example, by providing Level 2 and Level 3 payment data.
     *
     * @maps supplementary_data
     * @return self
     */
    public function setSupplementaryData(?SupplementaryData $supplementaryData): self
    {
        $this->supplementaryData = $supplementaryData;

        return $this;
    }

    /**
     * Returns Payments.
     * The collection of payments, or transactions, for a purchase unit in an order. For example,
     * authorized payments, captured payments, and refunds.
     */
    public function getPayments(): ?PaymentCollection
    {
        return $this->payments;
    }

    /**
     * Sets Payments.
     * The collection of payments, or transactions, for a purchase unit in an order. For example,
     * authorized payments, captured payments, and refunds.
     *
     * @maps payments
     * @return self
     */
    public function setPayments(?PaymentCollection $payments): self
    {
        $this->payments = $payments;

        return $this;
    }

    /**
     * Returns Most Recent Errors.
     * The error reason code and description that are the reason for the most recent order decline.
     *
     * @return array<mixed>|null
     */
    public function getMostRecentErrors(): ?array
    {
        return $this->mostRecentErrors;
    }

    /**
     * Sets Most Recent Errors.
     * The error reason code and description that are the reason for the most recent order decline.
     *
     * @param array<mixed>|null $mostRecentErrors
     *
     * @maps most_recent_errors
     * @return self
     */
    public function setMostRecentErrors(?array $mostRecentErrors): self
    {
        $this->mostRecentErrors = $mostRecentErrors;

        return $this;
    }
}
