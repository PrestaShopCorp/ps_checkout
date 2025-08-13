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

namespace PsCheckout\Core\PayPal\ShippingTracking\Repository;

use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingRecord;

interface ShippingTrackingRepositoryInterface
{
    /**
     * Check if tracking number already exists for this PayPal order
     *
     * @param string $payPalOrderId
     * @param string $trackingNumber
     *
     * @return bool
     */
    public function trackingNumberExists(string $payPalOrderId, string $trackingNumber): bool;

    /**
     * Save comprehensive tracking information
     *
     * @param int $psOrderId
     * @param string $payPalOrderId
     * @param string $payPalCaptureId
     * @param string $trackingNumber
     * @param int $carrierId
     * @param string $carrierName
     * @param array $items
     * @param string $payloadChecksum
     * @param string $status
     *
     * @return bool
     */
    public function saveComplete(
        int $psOrderId,
        string $payPalOrderId,
        string $payPalCaptureId,
        string $trackingNumber,
        int $carrierId,
        string $carrierName,
        array $items,
        string $payloadChecksum,
        string $status = 'PENDING',
        $paypalTrackingStatus = null
    ): bool;

    /**
     * Get pending tracking records to be sent to PayPal
     *
     * @return TrackingRecord[]
     */
    public function getPendingTracking(): array;

    /**
     * Update tracking record with tracker ID from PayPal
     *
     * @param int $id
     * @param string $trackerId
     *
     * @return bool
     */
    public function updateTrackerId(int $id, string $trackerId): bool;

    /**
     * Get all tracking records for a specific PrestaShop order
     *
     * @param string $orderId
     *
     * @return TrackingRecord[]
     */
    public function getTrackingByOrderId(string $orderId): array;

    /**
     * Update tracking status
     *
     * @param int $id
     * @param string $status
     *
     * @return bool
     */
    public function updateTrackingStatus(int $id, string $status): bool;

    /**
     * Mark tracking record as sent to PayPal with tracker ID
     *
     * @param int $id
     * @param string $trackerId
     *
     * @return bool
     */
    public function markAsSent(int $id, string $trackerId): bool;

    /**
     * Get tracking records that failed to be sent to PayPal
     *
     * @return TrackingRecord[]
     */
    public function getFailedTracking(): array;

    /**
     * Get tracking record by PayPal order ID and tracking number
     *
     * @param string $payPalOrderId
     * @param string $trackingNumber
     *
     * @return TrackingRecord|null
     */
    public function getByPayPalOrderAndTracking(string $payPalOrderId, string $trackingNumber);

    /**
     * Get existing tracking record by PayPal order ID only
     * This allows updating records when tracking numbers change
     *
     * @param string $payPalOrderId
     *
     * @return TrackingRecord|null
     */
    public function getByPayPalOrderId(string $payPalOrderId);

    /**
     * Update tracking record status as failed
     *
     * @param int $id
     * @param string $errorMessage
     *
     * @return bool
     */
    public function markAsFailed(int $id, string $errorMessage = ''): bool;

    /**
     * Get tracking records by status
     *
     * @param string $status
     *
     * @return TrackingRecord[]
     */
    public function getByStatus(string $status): array;

    /**
     * Update existing tracking record with complete data
     *
     * @param int $id
     * @param int $psOrderId
     * @param string $payPalOrderId
     * @param string $payPalCaptureId
     * @param string $trackingNumber
     * @param int $carrierId
     * @param string $carrierName
     * @param array $items
     * @param string $payloadChecksum
     * @param string $status
     *
     * @return bool
     */
    public function updateComplete(
        int $id,
        int $psOrderId,
        string $payPalOrderId,
        string $payPalCaptureId,
        string $trackingNumber,
        int $carrierId,
        string $carrierName,
        array $items,
        string $payloadChecksum,
        string $status = 'PENDING',
        $paypalTrackingStatus = null
    ): bool;
}
