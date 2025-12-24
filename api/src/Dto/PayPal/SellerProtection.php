<?php

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The level of protection offered as defined by [PayPal Seller Protection for Merchants](https://www.
 * paypal.com/us/webapps/mpp/security/seller-protection).
 */
class SellerProtection
{
    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string[]|null
     */
    private $disputeCategories;

    /**
     * Returns Status.
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal
     * Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * Indicates whether the transaction is eligible for seller protection. For information, see [PayPal
     * Seller Protection for Merchants](https://www.paypal.com/us/webapps/mpp/security/seller-protection).
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Dispute Categories.
     * An array of conditions that are covered for the transaction.
     *
     * @return string[]|null
     */
    public function getDisputeCategories(): ?array
    {
        return $this->disputeCategories;
    }

    /**
     * Sets Dispute Categories.
     * An array of conditions that are covered for the transaction.
     *
     * @maps dispute_categories
     *
     * @param string[]|null $disputeCategories
     */
    public function setDisputeCategories(?array $disputeCategories): void
    {
        $this->disputeCategories = $disputeCategories;
    }
}
