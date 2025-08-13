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

namespace PsCheckout\Core\PayPal\ShippingTracking\Action;

use Carrier;
use Order;
use OrderCarrier;
use PrestaShopCollection;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\ShippingTracking\Processor\ShipmentProcessorInterface;
use Psr\Log\LoggerInterface;

class AddTrackingAction implements AddTrackingActionInterface
{
    /**
     * @var ShipmentProcessorInterface
     */
    private $shipmentProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ShipmentProcessorInterface $shipmentProcessor,
        LoggerInterface $logger
    ) {
        $this->shipmentProcessor = $shipmentProcessor;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Order $order, Carrier $carrier)
    {
        try {
            // Get order carriers with tracking numbers
            $orderCarrierCollection = new PrestaShopCollection(OrderCarrier::class);
            $orderCarrierCollection->where('id_order', '=', $order->id);
            $orderCarrierCollection->where('id_carrier', '=', $carrier->id);

            foreach ($orderCarrierCollection->getResults() as $orderCarrier) {
                /** @var OrderCarrier $orderCarrier */
                if (empty($orderCarrier->tracking_number)) {
                    continue;
                }

                // Process each order carrier
                $this->shipmentProcessor->process($orderCarrier);
            }
        } catch (PsCheckoutException $exception) {
            // Log error but don't block execution
            $this->logger->error('Shipping tracking error: ' . $exception->getMessage(), [
                'order_id' => $order->id,
                'carrier_id' => $carrier->id,
                'exception' => $exception
            ]);
        } catch (\Exception $exception) {
            // Log unexpected errors
            $this->logger->error('Unexpected shipping tracking error: ' . $exception->getMessage(), [
                'order_id' => $order->id,
                'carrier_id' => $carrier->id,
                'exception' => $exception
            ]);
        }
    }
}
