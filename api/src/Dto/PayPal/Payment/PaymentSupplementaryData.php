<?php

namespace PsCheckout\Api\Dto\PayPal\Payment;

use PsCheckout\Api\Dto\PayPal\RelatedIdentifiers;

/**
 * The supplementary data.
 */
class PaymentSupplementaryData
{
    /**
     * @var RelatedIdentifiers|null
     */
    private $relatedIds;

    /**
     * Returns Related Ids.
     * Identifiers related to a specific resource.
     */
    public function getRelatedIds(): ?RelatedIdentifiers
    {
        return $this->relatedIds;
    }

    /**
     * Sets Related Ids.
     * Identifiers related to a specific resource.
     *
     * @maps related_ids
     */
    public function setRelatedIds(?RelatedIdentifiers $relatedIds): void
    {
        $this->relatedIds = $relatedIds;
    }
}
