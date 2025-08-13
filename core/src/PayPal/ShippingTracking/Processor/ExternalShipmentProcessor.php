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
use PsCheckout\Core\PayPal\ShippingTracking\Builder\TrackingPayloadBuilderInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Cache\ShippingTrackingCacheInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Repository\ShippingTrackingRepositoryInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Service\TrackingApiService;
use PsCheckout\Core\PayPal\ShippingTracking\Service\TrackingDatabaseHandler;
use PsCheckout\Core\PayPal\ShippingTracking\Validator\OrderTrackerValidatorInterface;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingStatus;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingData;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingApiRequest;
use PsCheckout\Core\PayPal\ShippingTracking\Action\AddTrackingActionInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Service\TrackingApiResult;
use Http\Client\Exception\HttpException;
use Psr\Log\LoggerInterface;

class ExternalShipmentProcessor implements ExternalShipmentProcessorInterface
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

    /**
     * @var AddTrackingActionInterface
     */
    private $addTrackingAction;

    public function __construct(
        OrderTrackerValidatorInterface $orderTrackerValidator,
        TrackingPayloadBuilderInterface $payloadBuilder,
        ShippingTrackingRepositoryInterface $shippingTrackingRepository,
        LoggerInterface $logger,
        ShippingTrackingCacheInterface $cache,
        TrackingApiService $trackingApiService,
        TrackingDatabaseHandler $trackingDatabaseHandler,
        AddTrackingActionInterface $addTrackingAction
    ) {
        $this->orderTrackerValidator = $orderTrackerValidator;
        $this->payloadBuilder = $payloadBuilder;
        $this->shippingTrackingRepository = $shippingTrackingRepository;
        $this->logger = $logger;
        $this->cache = $cache;
        $this->trackingApiService = $trackingApiService;
        $this->trackingDatabaseHandler = $trackingDatabaseHandler;
        $this->addTrackingAction = $addTrackingAction;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Order $order, array $externalShipmentData)
    {
        try {
            // Get carrier from order (we still need it for validation)
            $carrier = new Carrier($order->id_carrier);

            // Validate and get PayPal order data
            $orderData = $this->orderTrackerValidator->validate($order, $carrier);
            $payPalOrder = $orderData['paypal_order'];
            $capture = $orderData['capture'];

            // Get products from external data (source of truth)
            $products = $this->getProductsFromExternalData($externalShipmentData);

            // Get address from external data or order delivery address
            $address = $this->getAddressFromExternalData($externalShipmentData, $order);

            // Build payload using external data
            $payload = $this->payloadBuilder
                ->setOrderId($payPalOrder->getId())
                ->setCaptureId($capture->getId())
                ->setTrackingNumber($externalShipmentData['tracking_number'])
                ->setProducts($products)
                ->setOrderContext($order->id_lang, $order->id_shop)
                ->setCarrier($externalShipmentData['carrier']) // Use external carrier name directly
                ->setAddress($address)
                ->setNotifyPayer(false);

            // Set status from external data, fallback to 'SHIPPED' if not provided
            $status = $externalShipmentData['status'] ?? 'SHIPPED';
            $payload = $payload->setStatus($status);

            $payload = $payload->build();

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
                    $this->logger->info('Skipping external API call due to cached response with same payload for order ' . $order->id);

                    return;
                }
            }

            // Process external tracking API call using service (throws exceptions on error)
            $apiRequest = new TrackingApiRequest(
                $existingTracking,
                $payload,
                $payPalOrder->getId(),
                $order->id,
                true // throwOnError = true for external shipments
            );
            
            try {
                $apiResult = $this->trackingApiService->processTracking($apiRequest);
            } catch (HttpException $e) {
                // Check if this is a 400 status code and implement fallback
                if ($e->getResponse()->getStatusCode() === 400) {
                    $this->logger->warning('External tracking API call failed with 400 status code for order ' . $order->id . ', falling back to AddTrackingAction');
                    
                    try {
                        // Use the fallback action
                        $this->addTrackingAction->execute($order, $carrier);
                        
                        $this->logger->info('Fallback AddTrackingAction executed successfully for order ' . $order->id);
                        
                        // Create a success result for database handling
                        $apiResult = new TrackingApiResult(
                            false,
                            'fallback_success',
                            ['message' => 'Fallback to AddTrackingAction used due to 400 API error'],
                            null,
                            null
                        );
                    } catch (\Exception $fallbackException) {
                        $this->logger->error('Fallback AddTrackingAction also failed for order ' . $order->id . ': ' . $fallbackException->getMessage());

                        throw $fallbackException;
                    }
                } else {
                    // Re-throw other HTTP exceptions
                    throw $e;
                }
            }

            // Cache the API response with payload
            $cacheData = [
                'response' => $apiResult->getResponseData(),
                'status' => $apiResult->getStatus(),
                'payload' => $payload,
                'timestamp' => time(),
                'cache_key' => $cacheKey,
            ];
            $this->cache->set($cacheKey, $cacheData);
            
            // Save to database using service (only on success for external shipments)
            if ($apiResult->isSuccess() && $apiResult->getTrackerId()) {
                $trackingData = new TrackingData(
                    $order->id,
                    $payPalOrder->getId(),
                    $capture->getId(),
                    $externalShipmentData['tracking_number'],
                    $carrier->id,
                    $externalShipmentData['carrier'] ?? $carrier->name,
                    $products,
                    $payloadChecksum
                );
                
                $this->trackingDatabaseHandler->saveTrackingResult(
                    $apiResult,
                    $existingTracking,
                    $trackingData,
                    $status // Pass the PayPal tracking status from external data
                );
            }
        } catch (\InvalidArgumentException $e) {
            // Handle validation errors for required fields (SKU, quantity, name)
            $this->logger->error('External tracking validation failed for order ' . $order->id . ': ' . $e->getMessage());
            
            // Re-throw to ensure tracking fails
            throw $e;
        } catch (\Exception $e) {
            // Handle other errors (API failures, etc.)
            $this->logger->error('External tracking processing failed for order ' . $order->id . ': ' . $e->getMessage());
            
            // Re-throw to stop execution as requested
            throw $e;
        }
    }

    /**
     * Get products from external shipment data
     *
     * @param array $externalShipmentData
     *
     * @return array
     */
    private function getProductsFromExternalData(array $externalShipmentData): array
    {
        $products = [];
        
        if (!empty($externalShipmentData['products'])) {
            foreach ($externalShipmentData['products'] as $externalProduct) {
                // External data is source of truth, but we need to ensure we have correct data
                // Get product object to fill missing data if needed
                $productId = (int) ($externalProduct['id_product'] ?? 0);
                $productAttributeId = (int) ($externalProduct['id_product_attribute'] ?? 0);
                
                if ($productId > 0) {
                    $product = new \Product($productId);
                    if (\Validate::isLoadedObject($product)) {
                        $products[] = [
                            'id_product' => $productId,
                            'id_product_attribute' => $productAttributeId,
                            'reference' => $externalProduct['reference'] ?? $product->reference,
                            'quantity' => (int) $externalProduct['quantity'],
                            'name' => $externalProduct['name'] ?? $product->name,
                        ];
                    }
                }
            }
        }

        return $products;
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
