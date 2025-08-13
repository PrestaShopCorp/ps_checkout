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

use PsCheckout\Api\Http\OrderShipmentTrackingHttpClientInterface;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingRecord;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\PayPalTrackingStatus;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingApiRequest;
use Http\Client\Exception\HttpException;
use Psr\Log\LoggerInterface;

class TrackingApiService implements TrackingApiServiceInterface
{
    /**
     * @var OrderShipmentTrackingHttpClientInterface
     */
    private $orderShipmentTrackingClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        OrderShipmentTrackingHttpClientInterface $orderShipmentTrackingClient,
        LoggerInterface $logger
    ) {
        $this->orderShipmentTrackingClient = $orderShipmentTrackingClient;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function processTracking(TrackingApiRequest $request): TrackingApiResult
    {
        try {
            $existingTracking = $request->getExistingTracking();
            
            if ($existingTracking) {
                // Check if we have a tracker_id before attempting UPDATE
                if ($existingTracking->getTrackerId()) {
                    return $this->updateExistingTracking($existingTracking, $request);
                } else {
                    // No tracker_id means previous ADD failed, retry ADD instead of UPDATE
                    return $this->addNewTracking($request);
                }
            } else {
                return $this->addNewTracking($request);
            }
        } catch (\Exception $e) {
            $this->logger->error('API call failed for order ' . $request->getOrderId() . ': ' . $e->getMessage());
            if ($request->shouldThrowOnError()) {
                throw $e;
            }
            
            $existingTracking = $request->getExistingTracking();

            return new TrackingApiResult(
                false,
                'api_error',
                ['error' => $e->getMessage()],
                null,
                $existingTracking ? $existingTracking->getTrackerId() : null
            );
        }
    }

    /**
     * @param TrackingRecord $existingTracking
     * @param TrackingApiRequest $request
     * @return TrackingApiResult
     * @throws \Exception
     */
    private function updateExistingTracking(TrackingRecord $existingTracking, TrackingApiRequest $request): TrackingApiResult
    {
        $payload = $request->getPayload();
        
        // Get status from payload, fallback to 'SHIPPED' if not provided
        $status = $this->getValidatedStatus($payload['status'] ?? null);
        
        $updatePayload = [
            'order_id' => $request->getPayPalOrderId(),
            'status' => $status,
            'items' => $payload['items'] ?? [],
            'notify_payer' => false
        ];

        $apiResponse = $this->orderShipmentTrackingClient->updateTracking(
            $existingTracking->getTrackerId(),
            $updatePayload
        );

        $apiResponseData = json_decode($apiResponse->getBody()->getContents(), true) ?: [];
        $isSuccess = $apiResponse->getStatusCode() === 204;

        if ($isSuccess) {
            $this->logger->info('Tracking UPDATE API call successful for order ' . $request->getOrderId());

            return new TrackingApiResult(
                true,
                'success',
                $apiResponseData,
                $existingTracking->getTrackerId(),
                $existingTracking->getTrackerId()
            );
        } else {
            $this->logger->error('UPDATE API call returned unexpected status code for order ' . $request->getOrderId());
            $apiResponseData['error'] = 'Unexpected status code: ' . $apiResponse->getStatusCode();
            
            if ($request->shouldThrowOnError()) {
                throw new \Exception('Unexpected status code: ' . $apiResponse->getStatusCode());
            }
            
            return new TrackingApiResult(
                true,
                'api_error',
                $apiResponseData,
                $existingTracking->getTrackerId(),
                $existingTracking->getTrackerId()
            );
        }
    }

    /**
     * @param TrackingApiRequest $request
     * @return TrackingApiResult
     * @throws \Exception
     */
    private function addNewTracking(TrackingApiRequest $request): TrackingApiResult
    {
        try {
            $apiResponse = $this->orderShipmentTrackingClient->addTracking($request->getPayload());
            $apiResponseData = json_decode((string) $apiResponse->getBody(), true) ?: [];
            
            // Extract tracker ID from the correct location in response
            $trackerId = $this->extractTrackerIdFromResponse($apiResponseData);

            if (!empty($trackerId)) {
                $this->logger->info('Tracking ADD API call successful for order ' . $request->getOrderId() . ' with tracker ID: ' . $trackerId);

                return new TrackingApiResult(
                    false,
                    'success',
                    $apiResponseData,
                    $trackerId,
                    $trackerId
                );
            } else {
                $this->logger->error('ADD API call returned success but no tracker_id for order ' . $request->getOrderId());
                $apiResponseData['error'] = 'Missing tracker_id in API response';
                
                if ($request->shouldThrowOnError()) {
                    throw new \Exception('Missing tracker_id in API response');
                }
                
                return new TrackingApiResult(
                    false,
                    'api_error',
                    $apiResponseData,
                    null,
                    null
                );
            }
        } catch (HttpException $e) {
            // Re-throw other HTTP exceptions or if no fallback data available
            $this->logger->error('ADD API call failed for order ' . $request->getOrderId() . ': ' . $e->getMessage());
            
            if ($request->shouldThrowOnError()) {
                throw $e;
            }
            
            return new TrackingApiResult(
                false,
                'api_error',
                ['error' => $e->getMessage()],
                null,
                null
            );
        }
    }

    /**
     * Validate and get the tracking status with fallback
     *
     * @param string|null $status
     * @return string
     */
    private function getValidatedStatus($status): string
    {
        try {
            // Try to create PayPalTrackingStatus with validation
            $trackingStatus = PayPalTrackingStatus::createWithFallback($status);
            
            // Log if we're using fallback
            if (empty($status) || !PayPalTrackingStatus::isValid($status)) {
                $this->logger->info(sprintf(
                    'Invalid or missing status "%s", using fallback status "%s"',
                    $status ?? 'null',
                    $trackingStatus->getValue()
                ));
            }
            
            return $trackingStatus->getValue();
        } catch (\Exception $e) {
            // Fallback to default if any exception occurs
            $this->logger->warning(sprintf(
                'Exception during status validation: %s. Using default status "%s"',
                $e->getMessage(),
                PayPalTrackingStatus::getDefaultStatus()
            ));
            
            return PayPalTrackingStatus::getDefaultStatus();
        }
    }

    /**
     * Extract tracker ID from PayPal API response
     * Handles both direct tracker_id and nested purchase_units structure
     *
     * @param array $apiResponseData
     * @return string|null
     */
    private function extractTrackerIdFromResponse(array $apiResponseData)
    {
        // First try direct tracker_id (for direct tracking API responses)
        if (!empty($apiResponseData['tracker_id'])) {
            return $apiResponseData['tracker_id'];
        }

        // Then try nested structure: purchase_units[0].shipping.trackers[0].id
        if (!empty($apiResponseData['purchase_units'][0]['shipping']['trackers'][0]['id'])) {
            return $apiResponseData['purchase_units'][0]['shipping']['trackers'][0]['id'];
        }

        // Log the response structure for debugging
        $this->logger->warning('Could not extract tracker_id from API response structure: ' . json_encode($apiResponseData));
        
        return null;
    }
}
