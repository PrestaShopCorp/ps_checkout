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

use Order;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\ShippingTracking\Processor\ExternalShipmentProcessorInterface;
use Psr\Log\LoggerInterface;

class ProcessExternalShipmentAction implements ProcessExternalShipmentActionInterface
{
    /**
     * @var ExternalShipmentProcessorInterface
     */
    private $externalShipmentProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ExternalShipmentProcessorInterface $externalShipmentProcessor,
        LoggerInterface $logger
    ) {
        $this->externalShipmentProcessor = $externalShipmentProcessor;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Order $order, array $externalShipmentData)
    {
        try {
            // Validate that we have required external data
            if (empty($externalShipmentData['tracking_number'])) {
                $this->logger->warning('External shipment data missing tracking number for order ' . $order->id);

                return;
            }

            // Process the external shipment data
            $this->externalShipmentProcessor->process($order, $externalShipmentData);
        } catch (PsCheckoutException $exception) {
            // Log error and stop execution as requested
            $this->logger->error('External shipment processing error: ' . $exception->getMessage(), [
                'order_id' => $order->id,
                'exception' => $exception
            ]);

            throw $exception;
        } catch (\Exception $exception) {
            // Log unexpected errors and stop execution
            $this->logger->error('Unexpected external shipment error: ' . $exception->getMessage(), [
                'order_id' => $order->id,
                'exception' => $exception
            ]);

            throw $exception;
        }
    }
}
