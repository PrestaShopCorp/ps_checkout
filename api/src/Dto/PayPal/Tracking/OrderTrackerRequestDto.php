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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\OrderTrackerItem;

/**
 * The tracking details of an order.
 */
class OrderTrackerRequestDto
{
    /**
     * @var string|null
     */
    private $trackingNumber;

    /**
     * @var string|null
     */
    private $carrier;

    /**
     * @var string|null
     */
    private $carrierNameOther;

    /**
     * @var string
     */
    private $captureId;

    /**
     * @var bool|null
     */
    private $notifyPayer = false;

    /**
     * @var OrderTrackerItem[]|null
     */
    private $items;

    /**
     * @param string $captureId
     */
    public function __construct(string $captureId)
    {
        $this->captureId = $captureId;
    }

    /**
     * Returns Tracking Number.
     * The tracking number for the shipment. This property supports Unicode.
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    /**
     * Sets Tracking Number.
     * The tracking number for the shipment. This property supports Unicode.
     *
     * @maps tracking_number
     * @return self
     */
    public function setTrackingNumber(?string $trackingNumber): self
    {
        $this->trackingNumber = $trackingNumber;

        return $this;
    }

    /**
     * Returns Carrier.
     * The carrier for the shipment. Some carriers have a global version as well as local subsidiaries. The
     * subsidiaries are repeated over many countries and might also have an entry in the global list.
     * Choose the carrier for your country. If the carrier is not available for your country, choose the
     * global version of the carrier. If your carrier name is not in the list, set `carrier` to `OTHER` and
     * set carrier name in `carrier_name_other`. For allowed values, see Carriers.
     */
    public function getCarrier(): ?string
    {
        return $this->carrier;
    }

    /**
     * Sets Carrier.
     * The carrier for the shipment. Some carriers have a global version as well as local subsidiaries. The
     * subsidiaries are repeated over many countries and might also have an entry in the global list.
     * Choose the carrier for your country. If the carrier is not available for your country, choose the
     * global version of the carrier. If your carrier name is not in the list, set `carrier` to `OTHER` and
     * set carrier name in `carrier_name_other`. For allowed values, see Carriers.
     *
     * @maps carrier
     * @return self
     */
    public function setCarrier(?string $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * Returns Carrier Name Other.
     * The name of the carrier for the shipment. Provide this value only if the carrier parameter is OTHER.
     * This property supports Unicode.
     */
    public function getCarrierNameOther(): ?string
    {
        return $this->carrierNameOther;
    }

    /**
     * Sets Carrier Name Other.
     * The name of the carrier for the shipment. Provide this value only if the carrier parameter is OTHER.
     * This property supports Unicode.
     *
     * @maps carrier_name_other
     * @return self
     */
    public function setCarrierNameOther(?string $carrierNameOther): self
    {
        $this->carrierNameOther = $carrierNameOther;

        return $this;
    }

    /**
     * Returns Capture Id.
     * The PayPal capture ID.
     */
    public function getCaptureId(): string
    {
        return $this->captureId;
    }

    /**
     * Sets Capture Id.
     * The PayPal capture ID.
     *
     * @required
     * @maps capture_id
     * @return self
     */
    public function setCaptureId(string $captureId): self
    {
        $this->captureId = $captureId;

        return $this;
    }

    /**
     * Returns Notify Payer.
     * If true, PayPal will send an email notification to the payer of the PayPal transaction. The email
     * contains the tracking details provided through the Orders tracking API request. Independent of any
     * value passed for `notify_payer`, the payer may receive tracking notifications within the PayPal app,
     * based on the user's notification preferences.
     */
    public function getNotifyPayer(): ?bool
    {
        return $this->notifyPayer;
    }

    /**
     * Sets Notify Payer.
     * If true, PayPal will send an email notification to the payer of the PayPal transaction. The email
     * contains the tracking details provided through the Orders tracking API request. Independent of any
     * value passed for `notify_payer`, the payer may receive tracking notifications within the PayPal app,
     * based on the user's notification preferences.
     *
     * @maps notify_payer
     * @return self
     */
    public function setNotifyPayer(?bool $notifyPayer): self
    {
        $this->notifyPayer = $notifyPayer;

        return $this;
    }

    /**
     * Returns Items.
     * An array of details of items in the shipment.
     *
     * @return OrderTrackerItem[]|null
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Sets Items.
     * An array of details of items in the shipment.
     *
     * @maps items
     *
     * @param OrderTrackerItem[]|null $items
     * @return self
     */
    public function setItems(?array $items): self
    {
        $this->items = $items;

        return $this;
    }
}
