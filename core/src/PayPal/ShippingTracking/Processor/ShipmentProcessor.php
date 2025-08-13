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

namespace PsCheckout\Core\PayPal\ShippingTracking\Processor;

use Carrier;
use Order;
use OrderCarrier;
use OrderInvoice;
use PsCheckout\Core\PayPal\ShippingTracking\Builder\TrackingPayloadBuilderInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Cache\ShippingTrackingCacheInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Repository\ShippingTrackingRepositoryInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Service\TrackingApiService;
use PsCheckout\Core\PayPal\ShippingTracking\Service\TrackingDatabaseHandler;
use PsCheckout\Core\PayPal\ShippingTracking\Validator\OrderTrackerValidatorInterface;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingData;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingApiRequest;
use Psr\Log\LoggerInterface;

class ShipmentProcessor implements ShipmentProcessorInterface
{
    /**
     * @var OrderTrackerValidatorInterface
     */
    private $orderTrackerValidator;

    /**
     * @var TrackingPayloadBuilderInterface
     */
    private $payloadBuilder;

    /**
     * @var ShippingTrackingRepositoryInterface
     */
    private $shippingTrackingRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ShippingTrackingCacheInterface
     */
    private $cache;

    /**
     * @var TrackingApiService
     */
    private $trackingApiService;

    /**
     * @var TrackingDatabaseHandler
     */
    private $trackingDatabaseHandler;

    public function __construct(
        OrderTrackerValidatorInterface $orderTrackerValidator,
        TrackingPayloadBuilderInterface $payloadBuilder,
        ShippingTrackingRepositoryInterface $shippingTrackingRepository,
        LoggerInterface $logger,
        ShippingTrackingCacheInterface $cache,
        TrackingApiService $trackingApiService,
        TrackingDatabaseHandler $trackingDatabaseHandler
    ) {
        $this->orderTrackerValidator = $orderTrackerValidator;
        $this->payloadBuilder = $payloadBuilder;
        $this->shippingTrackingRepository = $shippingTrackingRepository;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->trackingApiService = $trackingApiService;
        $this->trackingDatabaseHandler = $trackingDatabaseHandler;
    }

    /**
     * {@inheritDoc}
     */
    public function process(OrderCarrier $orderCarrier)
    {
        try {
            // Get order and carrier from OrderCarrier
            $order = new Order($orderCarrier->id_order);
            $carrier = new Carrier($orderCarrier->id_carrier);
            $address = $this->getAddressFromExternalData([], $order);

            // Validate and get PayPal order data
            $orderData = $this->orderTrackerValidator->validate($order, $carrier);
            $payPalOrder = $orderData['paypal_order'];
            $capture = $orderData['capture'];

            // Get products from order invoice
            $products = $this->getProductsFromOrderCarrier($orderCarrier);

            // Build payload using carrier object
            $payload = $this->payloadBuilder
                ->setOrderId($payPalOrder->getId())
                ->setCaptureId($capture->getId())
                ->setTrackingNumber($orderCarrier->tracking_number)
                ->setProducts($products)
                ->setOrderContext($order->id_lang, $order->id_shop)
                ->setAddress($address)
                ->setCarrierFromCarrierObject($carrier)
                ->setNotifyPayer(false)
                ->build();

            $payloadChecksum = md5(json_encode($payload));

            // Check for existing tracking record by PayPal order ID only
            $existingTracking = $this->shippingTrackingRepository->getByPayPalOrderId($payPalOrder->getId());

            // Generate cache key using PayPal order ID only
            $cacheKey = $payPalOrder->getId();

            // Check cache before making API call
            if ($this->cache->has($cacheKey)) {
                $cachedResponse = $this->cache->getValue($cacheKey);
                // Skip if cache has same payload and status is completed
                if ($this->cache->shouldSkipApiCall($cachedResponse, $payload)) {
                    $this->logger->info('Skipping API call due to cached response with same payload for order ' . $orderCarrier->id_order);

                    return;
                }
            }

            // Process tracking API call using service
            $apiRequest = new TrackingApiRequest(
                $existingTracking,
                $payload,
                $payPalOrder->getId(),
                $orderCarrier->id_order,
                false // throwOnError = false for normal flow
            );
            
            $apiResult = $this->trackingApiService->processTracking($apiRequest);

            // Cache the API response with payload
            $cacheData = [
                'response' => $apiResult->getResponseData(),
                'status' => $apiResult->getStatus(),
                'payload' => $payload,
                'timestamp' => time(),
                'cache_key' => $cacheKey,
            ];
            $this->cache->set($cacheKey, $cacheData);
            
            // Save to database using service
            $trackingData = new TrackingData(
                $order->id,
                $payPalOrder->getId(),
                $capture->getId(),
                $orderCarrier->tracking_number,
                $carrier->id,
                $carrier->name,
                $products,
                $payloadChecksum
            );
            
            $this->trackingDatabaseHandler->saveTrackingResult(
                $apiResult,
                $existingTracking,
                $trackingData,
                'SHIPPED' // Normal processor sets PayPal tracking status to SHIPPED
            );
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors for required fields (SKU, quantity, name)
            $this->logger->error('Tracking validation failed for order ' . $orderCarrier->id_order . ': ' . $e->getMessage());
            
            // Save failed tracking attempt to database
            $this->saveFailedTracking($orderCarrier, 'VALIDATION_FAILED', $e->getMessage());
            
            // Re-throw to ensure tracking fails
            throw $e;
        } catch (\Exception $e) {
            // Handle other errors (API failures, etc.)
            $this->logger->error('Tracking processing failed for order ' . $orderCarrier->id_order . ': ' . $e->getMessage());
            
            // Save failed tracking attempt to database
            $this->saveFailedTracking($orderCarrier, 'PROCESSING_FAILED', $e->getMessage());
        }
    }

    /**
     * Get products from order carrier
     *
     * @param OrderCarrier $orderCarrier
     *
     * @return array
     */
    private function getProductsFromOrderCarrier(OrderCarrier $orderCarrier): array
    {
        $products = [];
        
        if ($orderCarrier->id_order_invoice) {
            $orderInvoice = new OrderInvoice($orderCarrier->id_order_invoice);
            $invoiceProducts = $orderInvoice->getProducts();
            
            foreach ($invoiceProducts as $product) {
                $products[] = [
                    'id_product' => (int) $product['id_product'],
                    'id_product_attribute' => (int) ($product['product_attribute_id'] ?? 0),
                    'reference' => $product['reference'] ?? '',
                    'quantity' => (int) $product['product_quantity'],
                    'name' => $product['product_name'] ?? '',
                ];
            }
        }

        return $products;
    }

    /**
     * Save failed tracking attempt to database
     *
     * @param OrderCarrier $orderCarrier
     * @param string $status
     * @param string $errorMessage
     */
    private function saveFailedTracking(OrderCarrier $orderCarrier, string $status, string $errorMessage)
    {
        try {
            $order = new Order($orderCarrier->id_order);
            $carrier = new Carrier($orderCarrier->id_carrier);
            
            // Try to get PayPal order data for logging
            $payPalOrderId = '';
            $captureId = '';
            
            try {
                $orderData = $this->orderTrackerValidator->validate($order, $carrier);
                $payPalOrderId = $orderData['paypal_order']->getId();
                $captureId = $orderData['capture']->getId();
            } catch (\Exception $e) {
                // Ignore validation errors when saving failed tracking
            }
            
            $this->shippingTrackingRepository->saveComplete(
                $order->id,
                $payPalOrderId,
                $captureId,
                $orderCarrier->tracking_number,
                $carrier->id,
                $carrier->name,
                [],
                md5($errorMessage),
                $status
            );
        } catch (\Exception $e) {
            // Log but don't throw - we don't want to break on database save failures
            $this->logger->error('Failed to save tracking failure to database: ' . $e->getMessage());
        }
    }

    /**
     * Get address from external data or order delivery address
     *
     * @param array $externalShipmentData
     * @param Order $order
     *
     * @return array
     */
    private function getAddressFromExternalData(array $externalShipmentData, Order $order): array
    {
        // If external data has address, use it (source of truth)
        if (!empty($externalShipmentData['address'])) {
            return $externalShipmentData['address'];
        }

        // Fallback to order delivery address
        $deliveryAddress = new \Address($order->id_address_delivery);
        if (\Validate::isLoadedObject($deliveryAddress)) {
            $country = new \Country($deliveryAddress->id_country);
            $state = new \State($deliveryAddress->id_state);

            return [
                'address_line_1' => $deliveryAddress->address1,
                'address_line_2' => $deliveryAddress->address2 ?: '',
                'admin_area_2' => $deliveryAddress->city,
                'admin_area_1' => $state->name ?: '',
                'postal_code' => $deliveryAddress->postcode,
                'country_code' => $country->iso_code,
            ];
        }

        return [];
    }
}
