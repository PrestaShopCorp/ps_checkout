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
use PsCheckout\Api\Dto\PayPal\RelatedIdentifiers;
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

    /**
     * Constructs a PaymentAuthorizationResponseDto from a raw PayPal API response array.
     * This avoids relying on PHPDoc-based type extraction (PhpDocExtractor) for LinkDescription[].
     *
     * @param array<string, mixed> $data
     */
    public static function fromPayPalApiResponse(array $data): self
    {
        $links = null;
        if (isset($data['links']) && is_array($data['links'])) {
            $links = array_map(static function (array $link) {
                return new LinkDescription(
                    (string) $link['href'],
                    (string) $link['rel'],
                    isset($link['method']) ? (string) $link['method'] : null
                );
            }, $data['links']);
        }

        $statusDetails = null;
        if (isset($data['status_details']) && is_array($data['status_details'])) {
            $statusDetails = new AuthorizationStatusDetails();
            $statusDetails->setReason(isset($data['status_details']['reason']) ? (string) $data['status_details']['reason'] : null);
        }

        $amount = null;
        if (isset($data['amount']) && is_array($data['amount'])) {
            $amount = new Money(
                (string) $data['amount']['currency_code'],
                (string) $data['amount']['value']
            );
        }

        $networkTransactionReference = null;
        if (isset($data['network_transaction_reference']) && is_array($data['network_transaction_reference'])) {
            $ref = $data['network_transaction_reference'];
            $networkTransactionReference = new NetworkTransaction();
            $networkTransactionReference->setId(isset($ref['id']) ? (string) $ref['id'] : null);
            $networkTransactionReference->setDate(isset($ref['date']) ? (string) $ref['date'] : null);
            $networkTransactionReference->setNetwork(isset($ref['network']) ? (string) $ref['network'] : null);
            $networkTransactionReference->setAcquirerReferenceNumber(isset($ref['acquirer_reference_number']) ? (string) $ref['acquirer_reference_number'] : null);
        }

        $sellerProtection = null;
        if (isset($data['seller_protection']) && is_array($data['seller_protection'])) {
            $sellerProtection = new SellerProtection();
            $sellerProtection->setStatus(isset($data['seller_protection']['status']) ? (string) $data['seller_protection']['status'] : null);
            $sellerProtection->setDisputeCategories(
                isset($data['seller_protection']['dispute_categories']) && is_array($data['seller_protection']['dispute_categories'])
                    ? $data['seller_protection']['dispute_categories']
                    : null
            );
        }

        $supplementaryData = null;
        if (isset($data['supplementary_data']) && is_array($data['supplementary_data'])) {
            $supplementaryData = new PaymentSupplementaryData();
            if (isset($data['supplementary_data']['related_ids']) && is_array($data['supplementary_data']['related_ids'])) {
                $rids = $data['supplementary_data']['related_ids'];
                $relatedIds = new RelatedIdentifiers();
                $relatedIds->setOrderId(isset($rids['order_id']) ? (string) $rids['order_id'] : null);
                $relatedIds->setAuthorizationId(isset($rids['authorization_id']) ? (string) $rids['authorization_id'] : null);
                $relatedIds->setCaptureId(isset($rids['capture_id']) ? (string) $rids['capture_id'] : null);
                $supplementaryData->setRelatedIds($relatedIds);
            }
        }

        $payee = null;
        if (isset($data['payee']) && is_array($data['payee'])) {
            $payee = new PayeeBase();
            $payee->setEmailAddress(isset($data['payee']['email_address']) ? (string) $data['payee']['email_address'] : null);
            $payee->setMerchantId(isset($data['payee']['merchant_id']) ? (string) $data['payee']['merchant_id'] : null);
        }

        return new self(
            (string) $data['id'],
            (string) $data['status'],
            $links,
            $statusDetails,
            isset($data['expiration_time']) ? (string) $data['expiration_time'] : null,
            isset($data['create_time']) ? (string) $data['create_time'] : null,
            isset($data['update_time']) ? (string) $data['update_time'] : null,
            $supplementaryData,
            $payee,
            $amount,
            isset($data['invoice_id']) ? (string) $data['invoice_id'] : null,
            isset($data['custom_id']) ? (string) $data['custom_id'] : null,
            $networkTransactionReference,
            $sellerProtection
        );
    }
}
