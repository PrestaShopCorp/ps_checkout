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

namespace PsCheckout\Core\PayPal\ShippingTracking\Builder\Node;

class TrackingBaseNodeBuilder implements TrackingBaseNodeBuilderInterface
{
    /**
     * @var string
     */
    private $orderId = '';

    /**
     * @var string
     */
    private $captureId = '';

    /**
     * @var string
     */
    private $trackingNumber = '';

    /**
     * @var string
     */
    private $carrier = '';

    /**
     * @var bool
     */
    private $notifyPayer = false;

    /**
     * @var string
     */
    private $status = '';

    /**
     * {@inheritDoc}
     */
    public function setOrderId(string $orderId): TrackingBaseNodeBuilderInterface
    {
        $this->orderId = $orderId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCaptureId(string $captureId): TrackingBaseNodeBuilderInterface
    {
        $this->captureId = $captureId;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTrackingNumber(string $trackingNumber): TrackingBaseNodeBuilderInterface
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setCarrier(string $carrier): TrackingBaseNodeBuilderInterface
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setNotifyPayer(bool $notifyPayer): TrackingBaseNodeBuilderInterface
    {
        $this->notifyPayer = $notifyPayer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus(string $status): TrackingBaseNodeBuilderInterface
    {
        $this->status = $status;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $payload = [
            'order_id' => $this->orderId,
            'capture_id' => $this->captureId,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'notify_payer' => $this->notifyPayer,
        ];

        // Only add status if it's not empty
        if (!empty($this->status)) {
            $payload['status'] = $this->status;
        }

        return $payload;
    }
}
