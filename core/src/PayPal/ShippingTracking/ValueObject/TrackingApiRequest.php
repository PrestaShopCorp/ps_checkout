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

namespace PsCheckout\Core\PayPal\ShippingTracking\ValueObject;

/**
 * Data Transfer Object for tracking API requests
 * Reduces parameter count in TrackingApiService methods
 */
class TrackingApiRequest
{
    /**
     * @var TrackingRecord|null
     */
    private $existingTracking;

    /**
     * @var array
     */
    private $payload;

    /**
     * @var string
     */
    private $payPalOrderId;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var bool
     */
    private $throwOnError;

    public function __construct(
        ?TrackingRecord $existingTracking,
        array $payload,
        string $payPalOrderId,
        int $orderId,
        bool $throwOnError = false
    ) {
        $this->existingTracking = $existingTracking;
        $this->payload = $payload;
        $this->payPalOrderId = $payPalOrderId;
        $this->orderId = $orderId;
        $this->throwOnError = $throwOnError;
    }

    public function getExistingTracking(): ?TrackingRecord
    {
        return $this->existingTracking;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getPayPalOrderId(): string
    {
        return $this->payPalOrderId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function shouldThrowOnError(): bool
    {
        return $this->throwOnError;
    }
}
