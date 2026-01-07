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

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\NetworkTransaction;
use PsCheckout\Api\Dto\PayPal\PayeeBase;
use PsCheckout\Api\Dto\PayPal\SellerProtection;

/**
 * The authorized payment transaction.
 */
class VoidAuthorizationResponseDto
{
    /**
     * @var value-of<AuthorizationStatus::STATUSES>|null
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
     * @param value-of<AuthorizationStatus::STATUSES> $status
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
     *
     * @return value-of<AuthorizationStatus::STATUSES>|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status for the authorized payment.
     *
     * @param value-of<AuthorizationStatus::STATUSES>|null $status
     *
     * @maps status
     * @return self
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
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
     * @return self
     */
    public function setStatusDetails(?AuthorizationStatusDetails $statusDetails): self
    {
        $this->statusDetails = $statusDetails;

        return $this;
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
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
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
     * @return self
     */
    public function setNetworkTransactionReference(?NetworkTransaction $networkTransactionReference): self
    {
        $this->networkTransactionReference = $networkTransactionReference;

        return $this;
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
     * @return self
     */
    public function setSellerProtection(?SellerProtection $sellerProtection): self
    {
        $this->sellerProtection = $sellerProtection;

        return $this;
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
     * @return self
     */
    public function setExpirationTime(?string $expirationTime): self
    {
        $this->expirationTime = $expirationTime;

        return $this;
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
     * @return self
     */
    public function setLinks(?array $links): self
    {
        $this->links = $links;

        return $this;
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
     * @return self
     */
    public function setCreateTime(?string $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
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
     * @return self
     */
    public function setUpdateTime(?string $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
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
     * @return self
     */
    public function setSupplementaryData(?PaymentSupplementaryData $supplementaryData): self
    {
        $this->supplementaryData = $supplementaryData;

        return $this;
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
     * @return self
     */
    public function setPayee(?PayeeBase $payee): self
    {
        $this->payee = $payee;

        return $this;
    }
}
