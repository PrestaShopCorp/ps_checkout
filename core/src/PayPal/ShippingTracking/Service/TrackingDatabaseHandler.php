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

namespace PsCheckout\Core\PayPal\ShippingTracking\Service;

use PsCheckout\Core\PayPal\ShippingTracking\Repository\ShippingTrackingRepositoryInterface;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingRecord;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingStatus;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingData;

use Psr\Log\LoggerInterface;

class TrackingDatabaseHandler implements TrackingDatabaseHandlerInterface
{
    /**
     * @var ShippingTrackingRepositoryInterface
     */
    private $shippingTrackingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ShippingTrackingRepositoryInterface $shippingTrackingRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ShippingTrackingRepositoryInterface $shippingTrackingRepository,
        LoggerInterface $logger
    ) {
        $this->shippingTrackingRepository = $shippingTrackingRepository;
        $this->logger = $logger;
    }

    /**
     * @param TrackingApiResult $result
     * @param TrackingRecord|null $existingTracking
     * @param TrackingData $trackingData
     * @param string|null $paypalTrackingStatus
     */
    public function saveTrackingResult(
        TrackingApiResult $result,
        $existingTracking,
        TrackingData $trackingData,
        $paypalTrackingStatus = null
    ) {
        if ($existingTracking) {
            $this->updateExistingRecord($result, $existingTracking, $trackingData, $paypalTrackingStatus);
        } else {
            $this->createNewRecord($result, $trackingData, $paypalTrackingStatus);
        }
    }

    /**
     * @param TrackingApiResult $result
     * @param TrackingRecord $existingTracking
     * @param TrackingData $trackingData
     * @param string|null $paypalTrackingStatus
     */
    private function updateExistingRecord(
        TrackingApiResult $result,
        TrackingRecord $existingTracking,
        TrackingData $trackingData,
        $paypalTrackingStatus = null
    ) {
        if ($result->isSuccess()) {
            // Only update database record if UPDATE API call was successful
            $this->shippingTrackingRepository->updateComplete(
                $existingTracking->getId(),
                $trackingData->getOrderId(),
                $trackingData->getPayPalOrderId(),
                $trackingData->getCaptureId(),
                $trackingData->getTrackingNumber(),
                $trackingData->getCarrierId(),
                $trackingData->getCarrierName(),
                $trackingData->getProducts(),
                $trackingData->getPayloadChecksum(),
                TrackingStatus::UPDATE_SENT,
                $paypalTrackingStatus ?? $existingTracking->getPaypalTrackingStatus()
            );

            if ($result->getTrackerId()) {
                $this->shippingTrackingRepository->markAsSent($existingTracking->getId(), $result->getTrackerId());
            }
            $this->logger->info('Updated existing tracking record to UPDATE_SENT with tracker ID: ' . $result->getTrackerId() . ' for order ' . $trackingData->getOrderId());
        } else {
            // If UPDATE fails, only update the status to UPDATE_FAILED, keep existing data
            $this->shippingTrackingRepository->updateTrackingStatus($existingTracking->getId(), TrackingStatus::UPDATE_FAILED);
            $this->logger->info('UPDATE API failed for order ' . $trackingData->getOrderId() . ' - status set to UPDATE_FAILED, tracking data unchanged');
        }
    }

    /**
     * @param TrackingApiResult $result
     * @param TrackingData $trackingData
     * @param string|null $paypalTrackingStatus
     */
    private function createNewRecord(
        TrackingApiResult $result,
        TrackingData $trackingData,
        $paypalTrackingStatus = null
    ) {
        // Set SENT status for successful ADD operations, FAILED for unsuccessful ones
        $status = $result->isSuccess() ? TrackingStatus::SENT : TrackingStatus::FAILED;
        
        $this->shippingTrackingRepository->saveComplete(
            $trackingData->getOrderId(),
            $trackingData->getPayPalOrderId(),
            $trackingData->getCaptureId(),
            $trackingData->getTrackingNumber(),
            $trackingData->getCarrierId(),
            $trackingData->getCarrierName(),
            $trackingData->getProducts(),
            $trackingData->getPayloadChecksum(),
            $status,
            $paypalTrackingStatus
        );

        if ($result->isSuccess() && $result->getTrackerId()) {
            $newTracking = $this->shippingTrackingRepository->getByPayPalOrderId($trackingData->getPayPalOrderId());
            if ($newTracking) {
                $this->shippingTrackingRepository->updateTrackerId($newTracking->getId(), $result->getTrackerId());
            }
            $this->logger->info('Created new tracking record with SENT status and tracker ID: ' . $result->getTrackerId() . ' for order ' . $trackingData->getOrderId());
        } else {
            $this->logger->info('Created new tracking record with FAILED status for order ' . $trackingData->getOrderId());
        }
    }
}
