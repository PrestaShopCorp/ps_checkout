<?php

namespace PsCheckout\Api\Dto\PayPal\Payment;

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\NetworkTransaction;
use PsCheckout\Api\Dto\PayPal\PayeeBase;
use PsCheckout\Api\Dto\PayPal\SellerProtection;

/**
 * The authorized payment transaction.
 */
class PaymentAuthorizationResponseDto
{
    /**
     * @var string|null
     */
    private $status;

    /**
     * @var AuthorizationStatusDetails|null
     */
    private $statusDetails;

    /**
     * @var string
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
     * @var string|null
     */
    private $expirationTime;

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
     * @var PaymentSupplementaryData|null
     */
    private $supplementaryData;

    /**
     * @var PayeeBase|null
     */
    private $payee;

    /**
     * @param LinkDescription[]|null $links
     */
    public function __construct(
        string $id,
        string $status,
        ?array $links = null,
        ?AuthorizationStatusDetails $statusDetails = null,
        ?string $expirationTime = null,
        ?string $createTime = null,
        ?string $updateTime = null,
        ?PaymentSupplementaryData $supplementaryData = null,
        ?PayeeBase $payee = null,
        ?Money $amount = null,
        ?string $invoiceId = null,
        ?string $customId = null,
        ?NetworkTransaction $networkTransactionReference = null,
        ?SellerProtection $sellerProtection = null
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->links = $links;
        $this->statusDetails = $statusDetails;
        $this->expirationTime = $expirationTime;
        $this->createTime = $createTime;
        $this->updateTime = $updateTime;
        $this->supplementaryData = $supplementaryData;
        $this->payee = $payee;
        $this->amount = $amount;
        $this->invoiceId = $invoiceId;
        $this->customId = $customId;
        $this->networkTransactionReference = $networkTransactionReference;
        $this->sellerProtection = $sellerProtection;
    }

    /**
     * Returns Status.
     * The status for the authorized payment.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status for the authorized payment.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Status Details.
     * The details of the authorized payment status.
     */
    public function getStatusDetails(): ?AuthorizationStatusDetails
    {
        return $this->statusDetails;
    }

    /**
     * Sets Status Details.
     * The details of the authorized payment status.
     *
     * @maps status_details
     */
    public function setStatusDetails(?AuthorizationStatusDetails $statusDetails): void
    {
        $this->statusDetails = $statusDetails;
    }

    /**
     * Returns Id.
     * The PayPal-generated ID for the authorized payment.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the authorized payment.
     *
     * @maps id
     */
    public function setId(string $id): void
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
     * Returns Expiration Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getExpirationTime(): ?string
    {
        return $this->expirationTime;
    }

    /**
     * Sets Expiration Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps expiration_time
     */
    public function setExpirationTime(?string $expirationTime): void
    {
        $this->expirationTime = $expirationTime;
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

    /**
     * Returns Supplementary Data.
     * The supplementary data.
     */
    public function getSupplementaryData(): ?PaymentSupplementaryData
    {
        return $this->supplementaryData;
    }

    /**
     * Sets Supplementary Data.
     * The supplementary data.
     *
     * @maps supplementary_data
     */
    public function setSupplementaryData(?PaymentSupplementaryData $supplementaryData): void
    {
        $this->supplementaryData = $supplementaryData;
    }

    /**
     * Returns Payee.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     */
    public function getPayee(): ?PayeeBase
    {
        return $this->payee;
    }

    /**
     * Sets Payee.
     * The details for the merchant who receives the funds and fulfills the order. The merchant is also
     * known as the payee.
     *
     * @maps payee
     */
    public function setPayee(?PayeeBase $payee): void
    {
        $this->payee = $payee;
    }
}
