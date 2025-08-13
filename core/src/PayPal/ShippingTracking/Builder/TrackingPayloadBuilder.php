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

namespace PsCheckout\Core\PayPal\ShippingTracking\Builder;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\ShippingTracking\Builder\Node\TrackingBaseNodeBuilderInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Builder\Node\TrackingItemsNodeBuilderInterface;
use PsCheckout\Core\PayPal\ShippingTracking\Builder\Node\TrackingCarrierModuleNodeBuilderInterface;

class TrackingPayloadBuilder implements TrackingPayloadBuilderInterface
{
    const CARRIER_OTHER = 'OTHER';

    /**
     * @var TrackingBaseNodeBuilderInterface
     */
    private $baseNodeBuilder;

    /**
     * @var TrackingItemsNodeBuilderInterface
     */
    private $itemsNodeBuilder;

    /**
     * @var TrackingCarrierModuleNodeBuilderInterface
     */
    private $carrierModuleNodeBuilder;

    /**
     * @var array
     */
    private $products = [];

    /**
     * @var array
     */
    private $address = [];

    /**
     * @var string
     */
    private $trackerId = '';

    /**
     * @param TrackingBaseNodeBuilderInterface $baseNodeBuilder
     * @param TrackingItemsNodeBuilderInterface $itemsNodeBuilder
     * @param TrackingCarrierModuleNodeBuilderInterface $carrierModuleNodeBuilder
     */
    public function __construct(
        TrackingBaseNodeBuilderInterface $baseNodeBuilder,
        TrackingItemsNodeBuilderInterface $itemsNodeBuilder,
        TrackingCarrierModuleNodeBuilderInterface $carrierModuleNodeBuilder
    ) {
        $this->baseNodeBuilder = $baseNodeBuilder;
        $this->itemsNodeBuilder = $itemsNodeBuilder;
        $this->carrierModuleNodeBuilder = $carrierModuleNodeBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function build(string $operation = self::OPERATION_ADD): array
    {
        return $this->buildAddPayload();
    }

    /**
     * Build payload for adding tracking information
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    private function buildAddPayload(): array
    {
        $this->validateAddPayload();

        // Build base payload
        $payload = $this->baseNodeBuilder->build();

        // Add items
        $this->itemsNodeBuilder->setProducts($this->products);
        $itemsPayload = $this->itemsNodeBuilder->build();
        $payload = array_merge($payload, $itemsPayload);

        // Add carrier module data
        $carrierModulePayload = $this->carrierModuleNodeBuilder->build();
        $payload = array_merge($payload, $carrierModulePayload);

        // Add address if provided
        if (!empty($this->address)) {
            $payload['address'] = $this->address;
        }

        return $payload;
    }

    /**
     * Validate required fields for add operation
     *
     * @throws PsCheckoutException
     */
    private function validateAddPayload()
    {
        $basePayload = $this->baseNodeBuilder->build();

        if (empty($basePayload['order_id'])) {
            throw new PsCheckoutException('Order ID is required for tracking payload');
        }

        if (empty($basePayload['capture_id'])) {
            throw new PsCheckoutException('Capture ID is required for tracking payload');
        }

        if (empty($basePayload['tracking_number'])) {
            throw new PsCheckoutException('Tracking number is required for tracking payload');
        }

        if (empty($basePayload['carrier'])) {
            throw new PsCheckoutException('Carrier is required for tracking payload');
        }

        if (empty($this->products)) {
            throw new PsCheckoutException('Products are required for tracking payload');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderId(string $orderId): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setOrderId($orderId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCaptureId(string $captureId): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setCaptureId($captureId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTrackingNumber(string $trackingNumber): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setTrackingNumber($trackingNumber);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrier(string $carrier): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setCarrier($carrier);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setProducts(array $products): TrackingPayloadBuilderInterface
    {
        $this->products = $products;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderContext(int $languageId, int $shopId): TrackingPayloadBuilderInterface
    {
        $this->itemsNodeBuilder->setOrderContext($languageId, $shopId);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress(array $address): TrackingPayloadBuilderInterface
    {
        $this->address = $address;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrierNameOther(string $carrierNameOther): TrackingPayloadBuilderInterface
    {
        $this->carrierModuleNodeBuilder->setCarrierNameOther($carrierNameOther);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setNotifyPayer(bool $notifyPayer): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setNotifyPayer($notifyPayer);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus(string $status): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder->setStatus($status);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTrackerId(string $trackerId): TrackingPayloadBuilderInterface
    {
        $this->trackerId = $trackerId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrierModule(string $name, string $version, string $deliveryOption): TrackingPayloadBuilderInterface
    {
        $this->carrierModuleNodeBuilder->setCarrierModule($name, $version, $deliveryOption);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrierFromCarrierObject(\Carrier $carrier): TrackingPayloadBuilderInterface
    {
        // Always set carrier to "OTHER" for hook usage
        $this->setCarrier(self::CARRIER_OTHER);
        
        // Always set carrier_name_other to the actual carrier name when carrier is "OTHER"
        $this->setCarrierNameOther($carrier->name);

        // Handle carrier module logic
        $deliveryOptionName = $carrier->name;
        $carrierModuleName = $carrier->external_module_name ?? null;

        if ($carrierModuleName) {
            // External module case
            try {
                $moduleId = \Module::getModuleIdByName($carrierModuleName);
                if ($moduleId) {
                    $module = new \Module($moduleId);
                    $version = $module->version;
                }

                $this->setCarrierModule($carrierModuleName, $version ?? '', $deliveryOptionName);
            } catch (\Exception $e) {
                // Fallback if module loading fails
                $this->setCarrierModule($carrierModuleName, '', $deliveryOptionName);
            }
        } else {
            // Core delivery option case - no module name, no version
            $this->setCarrierModule('', '', $deliveryOptionName);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function reset(): TrackingPayloadBuilderInterface
    {
        $this->baseNodeBuilder
            ->setOrderId('')
            ->setCaptureId('')
            ->setTrackingNumber('')
            ->setCarrier('')
            ->setNotifyPayer(false)
            ->setStatus('');

        $this->itemsNodeBuilder->setProducts([]);

        $this->carrierModuleNodeBuilder
            ->setCarrierNameOther('')
            ->setCarrierModule('', '', '');

        $this->products = [];
        $this->address = [];
        $this->trackerId = '';

        return $this;
    }
}
