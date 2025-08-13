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

/**
 * Data Transfer Object for tracking information
 * Reduces parameter count in method signatures
 */
class TrackingData
{
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
    private $captureId;

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
     * @var array
     */
    private $products;

    /**
     * @var string
     */
    private $payloadChecksum;

    /**
     * @param int $orderId
     * @param string $payPalOrderId
     * @param string $captureId
     * @param string $trackingNumber
     * @param int $carrierId
     * @param string $carrierName
     * @param array $products
     * @param string $payloadChecksum
     */
    public function __construct(
        $orderId,
        $payPalOrderId,
        $captureId,
        $trackingNumber,
        $carrierId,
        $carrierName,
        array $products,
        $payloadChecksum
    ) {
        $this->orderId = $orderId;
        $this->payPalOrderId = $payPalOrderId;
        $this->captureId = $captureId;
        $this->trackingNumber = $trackingNumber;
        $this->carrierId = $carrierId;
        $this->carrierName = $carrierName;
        $this->products = $products;
        $this->payloadChecksum = $payloadChecksum;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string
     */
    public function getPayPalOrderId()
    {
        return $this->payPalOrderId;
    }

    /**
     * @return string
     */
    public function getCaptureId()
    {
        return $this->captureId;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @return int
     */
    public function getCarrierId()
    {
        return $this->carrierId;
    }

    /**
     * @return string
     */
    public function getCarrierName()
    {
        return $this->carrierName;
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return string
     */
    public function getPayloadChecksum()
    {
        return $this->payloadChecksum;
    }
}
