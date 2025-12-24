<?php

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Identifiers related to a specific resource.
 */
class RelatedIdentifiers
{
    /**
     * @var string|null
     */
    private $orderId;

    /**
     * @var string|null
     */
    private $authorizationId;

    /**
     * @var string|null
     */
    private $captureId;

    /**
     * Returns Order Id.
     * Order ID related to the resource.
     */
    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    /**
     * Sets Order Id.
     * Order ID related to the resource.
     *
     * @maps order_id
     */
    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * Returns Authorization Id.
     * Authorization ID related to the resource.
     */
    public function getAuthorizationId(): ?string
    {
        return $this->authorizationId;
    }

    /**
     * Sets Authorization Id.
     * Authorization ID related to the resource.
     *
     * @maps authorization_id
     */
    public function setAuthorizationId(?string $authorizationId): void
    {
        $this->authorizationId = $authorizationId;
    }

    /**
     * Returns Capture Id.
     * Capture ID related to the resource.
     */
    public function getCaptureId(): ?string
    {
        return $this->captureId;
    }

    /**
     * Sets Capture Id.
     * Capture ID related to the resource.
     *
     * @maps capture_id
     */
    public function setCaptureId(?string $captureId): void
    {
        $this->captureId = $captureId;
    }
}
