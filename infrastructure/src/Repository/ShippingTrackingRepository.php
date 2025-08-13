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

namespace PsCheckout\Infrastructure\Repository;

use PsCheckout\Core\PayPal\ShippingTracking\Repository\ShippingTrackingRepositoryInterface;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingRecord;

class ShippingTrackingRepository implements ShippingTrackingRepositoryInterface
{
    /**
     * @var string
     */
    const TABLE_NAME = 'pscheckout_tracking';

    /**
     * Database status constants
     */
    const STATUS_PENDING = 'PENDING';

    const STATUS_SENT = 'SENT';

    const STATUS_FAILED = 'FAILED';

    /**
     * @var \Db
     */
    private $db;

    /**
     * @param \Db $db
     */
    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function trackingNumberExists(string $payPalOrderId, string $trackingNumber): bool
    {
        $query = new \DbQuery();
        $query->select('COUNT(*)');
        $query->from(self::TABLE_NAME);
        $query->where('paypal_order_id = "' . pSQL($payPalOrderId) . '" AND tracking_number = "' . pSQL($trackingNumber) . '"');

        return (bool) $this->db->getValue($query);
    }

    /**
     * {@inheritDoc}
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
        string $status = self::STATUS_PENDING,
        $paypalTrackingStatus = null
    ): bool {
        $data = [
            'id_order' => $psOrderId,
            'paypal_order_id' => pSQL($payPalOrderId),
            'paypal_capture_id' => pSQL($payPalCaptureId),
            'tracking_number' => pSQL($trackingNumber),
            'carrier_id' => $carrierId,
            'carrier_name' => pSQL($carrierName),
            'items' => pSQL(json_encode($items)),
            'status' => pSQL($status),
            'payload_checksum' => pSQL($payloadChecksum),
            'sent_to_paypal' => 0,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        if ($paypalTrackingStatus !== null) {
            $data['paypal_tracking_status'] = pSQL($paypalTrackingStatus);
        }

        return $this->db->insert(self::TABLE_NAME, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function getPendingTracking(): array
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('sent_to_paypal = 0');
        $query->orderBy('date_add ASC');

        $result = $this->db->executeS($query);

        if (empty($result)) {
            return [];
        }

        return array_map(function ($trackingData) {
            return $this->buildTrackingRecord($trackingData);
        }, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTrackerId(int $id, string $trackerId): bool
    {
        $data = [
            'tracker_id' => pSQL($trackerId),
            'sent_to_paypal' => 1,
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackingByOrderId(string $orderId): array
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('id_order = ' . (int) $orderId);
        $query->orderBy('date_add ASC');

        $result = $this->db->executeS($query);

        if (empty($result)) {
            return [];
        }

        return array_map(function ($trackingData) {
            return $this->buildTrackingRecord($trackingData);
        }, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function updateTrackingStatus(int $id, string $status): bool
    {
        $data = [
            'status' => pSQL($status),
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $id);
    }

    /**
     * {@inheritDoc}
     */
    public function markAsSent(int $id, string $trackerId): bool
    {
        $data = [
            'tracker_id' => pSQL($trackerId),
            'status' => self::STATUS_SENT,
            'sent_to_paypal' => 1,
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getFailedTracking(): array
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('status = "' . self::STATUS_FAILED . '"');
        $query->orderBy('date_add ASC');

        $result = $this->db->executeS($query);

        if (empty($result)) {
            return [];
        }

        return array_map(function ($trackingData) {
            return $this->buildTrackingRecord($trackingData);
        }, $result);
    }

    /**
     * {@inheritDoc}
     */
    public function getByPayPalOrderAndTracking(string $payPalOrderId, string $trackingNumber)
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('paypal_order_id = "' . pSQL($payPalOrderId) . '" AND tracking_number = "' . pSQL($trackingNumber) . '"');

        $result = $this->db->getRow($query);

        if (!is_array($result) || empty($result)) {
            return null;
        }

        return $this->buildTrackingRecord($result);
    }

    /**
     * {@inheritDoc}
     */
    public function getByPayPalOrderId(string $payPalOrderId)
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('paypal_order_id = "' . pSQL($payPalOrderId) . '"');

        $result = $this->db->getRow($query);

        if (!is_array($result) || empty($result)) {
            return null;
        }

        return $this->buildTrackingRecord($result);
    }

    /**
     * {@inheritDoc}
     */
    public function markAsFailed(int $id, string $errorMessage = ''): bool
    {
        $data = [
            'status' => self::STATUS_FAILED,
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        // Store error message in items field if provided (temporary solution)
        if (!empty($errorMessage)) {
            $data['items'] = pSQL(json_encode(['error' => $errorMessage]));
        }

        return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getByStatus(string $status): array
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(self::TABLE_NAME);
        $query->where('status = "' . pSQL($status) . '"');
        $query->orderBy('date_add ASC');

        $result = $this->db->executeS($query);

        if (empty($result)) {
            return [];
        }

        return array_map(function ($trackingData) {
            return $this->buildTrackingRecord($trackingData);
        }, $result);
    }

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
        string $status = self::STATUS_PENDING,
        $paypalTrackingStatus = null
    ): bool {
        $data = [
            'id_order' => $psOrderId,
            'paypal_order_id' => pSQL($payPalOrderId),
            'paypal_capture_id' => pSQL($payPalCaptureId),
            'tracking_number' => pSQL($trackingNumber),
            'carrier_id' => $carrierId,
            'carrier_name' => pSQL($carrierName),
            'items' => pSQL(json_encode($items)),
            'status' => pSQL($status),
            'payload_checksum' => pSQL($payloadChecksum),
            'sent_to_paypal' => 0,
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        if ($paypalTrackingStatus !== null) {
            $data['paypal_tracking_status'] = pSQL($paypalTrackingStatus);
        }

        return $this->db->update(self::TABLE_NAME, $data, 'id = ' . (int) $id);
    }

    /**
     * Build TrackingRecord from database array
     *
     * @param array $data
     *
     * @return TrackingRecord
     */
    private function buildTrackingRecord(array $data): TrackingRecord
    {
        return TrackingRecord::createFromArray($data);
    }
}
