<?php

namespace PsCheckout\Api\Dto\PayPal\Payment;

/**
 * The details of the authorized payment status.
 */
class AuthorizationStatusDetails
{
    /**
     * @var string|null
     */
    private $reason;

    /**
     * Returns Reason.
     * The reason why the authorized status is `PENDING`.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Sets Reason.
     * The reason why the authorized status is `PENDING`.
     *
     * @maps reason
     */
    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
