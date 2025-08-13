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

class TrackingRecord
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $orderId;

    /**
     * @var string
     */
    private $payPalOrderId;

    /**
     * @var string
     */
    private $payPalCaptureId;

    /**
     * @var string
     */
    private $trackingNumber;

    /**
     * @var int
     */
    private $carrierId;

    /**
     * @var string
     */
    private $carrierName;

    /**
     * @var string
     */
    private $trackerId;

    /**
     * @var array
     */
    private $items;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string|null
     */
    private $paypalTrackingStatus;

    /**
     * @var string
     */
    private $payloadChecksum;

    /**
     * @var bool
     */
    private $sentToPayPal;

    /**
     * @var string
     */
    private $dateAdd;

    /**
     * @var string
     */
    private $dateUpdate;

    public function __construct(
        int $id,
        int $orderId,
        string $payPalOrderId,
        string $payPalCaptureId,
        string $trackingNumber,
        int $carrierId,
        string $carrierName,
        string $trackerId,
        array $items,
        string $status,
        $paypalTrackingStatus,
        string $payloadChecksum,
        bool $sentToPayPal,
        string $dateAdd,
        string $dateUpdate
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->payPalOrderId = $payPalOrderId;
        $this->payPalCaptureId = $payPalCaptureId;
        $this->trackingNumber = $trackingNumber;
        $this->carrierId = $carrierId;
        $this->carrierName = $carrierName;
        $this->trackerId = $trackerId;
        $this->items = $items;
        $this->status = $status;
        $this->paypalTrackingStatus = $paypalTrackingStatus;
        $this->payloadChecksum = $payloadChecksum;
        $this->sentToPayPal = $sentToPayPal;
        $this->dateAdd = $dateAdd;
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPayPalOrderId(): string
    {
        return $this->payPalOrderId;
    }

    /**
     * @return string
     */
    public function getPayPalCaptureId(): string
    {
        return $this->payPalCaptureId;
    }

    /**
     * @return string
     */
    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    /**
     * @return int
     */
    public function getCarrierId(): int
    {
        return $this->carrierId;
    }

    /**
     * @return string
     */
    public function getCarrierName(): string
    {
        return $this->carrierName;
    }

    /**
     * @return string
     */
    public function getTrackerId(): string
    {
        return $this->trackerId;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getPaypalTrackingStatus()
    {
        return $this->paypalTrackingStatus;
    }

    /**
     * @return string
     */
    public function getPayloadChecksum(): string
    {
        return $this->payloadChecksum;
    }

    /**
     * @return bool
     */
    public function isSentToPayPal(): bool
    {
        return $this->sentToPayPal;
    }

    /**
     * @return string
     */
    public function getDateAdd(): string
    {
        return $this->dateAdd;
    }

    /**
     * @return string
     */
    public function getDateUpdate(): string
    {
        return $this->dateUpdate;
    }

    /**
     * Check if tracking record has tracker ID from PayPal
     *
     * @return bool
     */
    public function hasTrackerId(): bool
    {
        return !empty($this->trackerId);
    }

    /**
     * Check if tracking record is in pending status
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    /**
     * Check if tracking record is sent
     *
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->status === 'SENT';
    }

    /**
     * Check if tracking record failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    /**
     * Create TrackingRecord from database array
     *
     * @param array $data
     *
     * @return TrackingRecord
     */
    public static function createFromArray(array $data): TrackingRecord
    {
        return new self(
            (int) ($data['id'] ?? 0),
            (int) ($data['id_order'] ?? 0),
            $data['paypal_order_id'] ?? '',
            $data['paypal_capture_id'] ?? '',
            $data['tracking_number'] ?? '',
            (int) ($data['carrier_id'] ?? 0),
            $data['carrier_name'] ?? '',
            $data['tracker_id'] ?? '',
            json_decode($data['items'] ?? '[]', true) ?: [],
            $data['status'] ?? 'PENDING',
            $data['paypal_tracking_status'] ?? null,
            $data['payload_checksum'] ?? '',
            (bool) ($data['sent_to_paypal'] ?? false),
            $data['date_add'] ?? '',
            $data['date_upd'] ?? ''
        );
    }
}
