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
 * The purchase unit request. Includes required information for the payment contract.
 */
class PurchaseUnitRequest
{
    /**
     * @var string|null
     */
    private $referenceId;

    /**
     * @var AmountWithBreakdown
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
    private $softDescriptor;

    /**
     * @var ItemRequest[]|null
     */
    private $items;

    /**
     * @var ShippingDetails|null
     */
    private $shipping;

    /**
     * @var SupplementaryData|null
     */
    private $supplementaryData;

    /**
     * @param AmountWithBreakdown $amount
     */
    public function __construct(
        AmountWithBreakdown $amount
    ) {
        $this->amount = $amount;
    }

    /**
     * Returns Reference Id.
     * The API caller-provided external ID for the purchase unit. Required for multiple purchase units when
     * you must update the order through `PATCH`. If you omit this value and the order contains only one
     * purchase unit, PayPal sets this value to `default`.
     */
    public function getReferenceId(): ?string
    {
        return $this->referenceId;
    }

    /**
     * Sets Reference Id.
     * The API caller-provided external ID for the purchase unit. Required for multiple purchase units when
     * you must update the order through `PATCH`. If you omit this value and the order contains only one
     * purchase unit, PayPal sets this value to `default`.
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
    public function getAmount(): AmountWithBreakdown
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
     * @required
     * @maps amount
     * @return self
     */
    public function setAmount(AmountWithBreakdown $amount): self
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
     * This field supports up to 3,000 characters, but any content beyond 127 characters (including spaces)
     * will be truncated. The 127 character limit is reflected in the response representation of this field.
     * The purchase description. The maximum length of the character is dependent on the type of
     * characters used. The character length is specified assuming a US ASCII character. Depending on type
     * of character; (e.g. accented character, Japanese characters) the number of characters that that can
     * be specified as input might not equal the permissible max length.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * This field supports up to 3,000 characters, but any content beyond 127 characters (including spaces)
     * will be truncated. The 127 character limit is reflected in the response representation of this field.
     * The purchase description. The maximum length of the character is dependent on the type of
     * characters used. The character length is specified assuming a US ASCII character. Depending on type
     * of character; (e.g. accented character, Japanese characters) the number of characters that that can
     * be specified as input might not equal the permissible max length.
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
     * The API caller-provided external ID. Used to reconcile client transactions with PayPal transactions.
     * Appears in transaction and settlement reports but is not visible to the payer.
     */
    public function getCustomId(): ?string
    {
        return $this->customId;
    }

    /**
     * Sets Custom Id.
     * The API caller-provided external ID. Used to reconcile client transactions with PayPal transactions.
     * Appears in transaction and settlement reports but is not visible to the payer.
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
     * The API caller-provided external invoice number for this order. Appears in both the payer's
     * transaction history and the emails that the payer receives. invoice_id values are required to be
     * unique within each merchant account by default. Although the uniqueness validation is configurable,
     * disabling this behavior will remove the account's ability to use invoice_id in other APIs as an
     * identifier. It is highly recommended to keep a unique invoice_id for each Order.
     */
    public function getInvoiceId(): ?string
    {
        return $this->invoiceId;
    }

    /**
     * Sets Invoice Id.
     * The API caller-provided external invoice number for this order. Appears in both the payer's
     * transaction history and the emails that the payer receives. invoice_id values are required to be
     * unique within each merchant account by default. Although the uniqueness validation is configurable,
     * disabling this behavior will remove the account's ability to use invoice_id in other APIs as an
     * identifier. It is highly recommended to keep a unique invoice_id for each Order.
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
     * Returns Soft Descriptor.
     * This field supports up to 127 characters, but any content beyond 22 characters (including spaces)
     * will be truncated. The 22 character limit is reflected in the response representation of this field.
     * The soft descriptor is the dynamic text used to construct the statement descriptor that appears on a
     * payer's card statement. If an Order is paid using the "PayPal Wallet", the statement descriptor will
     * appear in following format on the payer's card statement:
     * PAYPAL_prefix+(space)+merchant_descriptor+(space)+ soft_descriptor Note: The merchant descriptor is
     * the descriptor of the merchant’s payment receiving preferences which can be seen by logging into the
     * merchant account https://www.sandbox.paypal.com/businessprofile/settings/info/edit The PAYPAL prefix
     * uses 8 characters. Only the first 22 characters will be displayed in the statement. For example, if:
     * The PayPal prefix toggle is PAYPAL *. The merchant descriptor in the profile is Janes Gift. The soft
     * descriptor is 800-123-1234. Then, the statement descriptor on the card is PAYPAL * Janes Gift 80.
     */
    public function getSoftDescriptor(): ?string
    {
        return $this->softDescriptor;
    }

    /**
     * Sets Soft Descriptor.
     * This field supports up to 127 characters, but any content beyond 22 characters (including spaces)
     * will be truncated. The 22 character limit is reflected in the response representation of this field.
     * The soft descriptor is the dynamic text used to construct the statement descriptor that appears on a
     * payer's card statement. If an Order is paid using the "PayPal Wallet", the statement descriptor will
     * appear in following format on the payer's card statement:
     * PAYPAL_prefix+(space)+merchant_descriptor+(space)+ soft_descriptor Note: The merchant descriptor is
     * the descriptor of the merchant’s payment receiving preferences which can be seen by logging into the
     * merchant account https://www.sandbox.paypal.com/businessprofile/settings/info/edit The PAYPAL prefix
     * uses 8 characters. Only the first 22 characters will be displayed in the statement. For example, if:
     * The PayPal prefix toggle is PAYPAL *. The merchant descriptor in the profile is Janes Gift. The soft
     * descriptor is 800-123-1234. Then, the statement descriptor on the card is PAYPAL * Janes Gift 80.
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
     * @return ItemRequest[]|null
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
     * @param ItemRequest[]|null $items
     * @return self
     */
    public function setItems(?array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Adds an Item to the purchase unit.
     *
     * @param ItemRequest $item
     * @return self
     */
    public function addItem(ItemRequest $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Returns Shipping.
     * The shipping details.
     */
    public function getShipping(): ?ShippingDetails
    {
        return $this->shipping;
    }

    /**
     * Sets Shipping.
     * The shipping details.
     *
     * @maps shipping
     * @return self
     */
    public function setShipping(?ShippingDetails $shipping): self
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
}
