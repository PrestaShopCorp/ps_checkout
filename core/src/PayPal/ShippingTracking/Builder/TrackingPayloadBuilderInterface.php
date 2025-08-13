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

interface TrackingPayloadBuilderInterface
{
    const OPERATION_ADD = 'add';

    const OPERATION_UPDATE = 'update';

    const OPERATION_CANCEL = 'cancel';

    /**
     * Builds the tracking payload for PayPal API
     *
     * @param string $operation Operation type: 'add', 'update', or 'cancel'
     *
     * @return array the constructed payload
     *
     * @throws PsCheckoutException if required fields are missing
     */
    public function build(string $operation = self::OPERATION_ADD): array;

    /**
     * Set PayPal order ID (used for URL construction)
     *
     * @param string $orderId
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setOrderId(string $orderId): TrackingPayloadBuilderInterface;

    /**
     * Set PayPal capture ID
     *
     * @param string $captureId
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setCaptureId(string $captureId): TrackingPayloadBuilderInterface;

    /**
     * Set tracking number
     *
     * @param string $trackingNumber
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setTrackingNumber(string $trackingNumber): TrackingPayloadBuilderInterface;

    /**
     * Set carrier name
     *
     * @param string $carrier
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setCarrier(string $carrier): TrackingPayloadBuilderInterface;

    /**
     * Set products list
     *
     * @param array $products
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setProducts(array $products): TrackingPayloadBuilderInterface;

    /**
     * Set custom carrier name for "OTHER" carriers
     *
     * @param string $carrierNameOther
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setCarrierNameOther(string $carrierNameOther): TrackingPayloadBuilderInterface;

    /**
     * Set whether to notify payer
     *
     * @param bool $notifyPayer
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setNotifyPayer(bool $notifyPayer): TrackingPayloadBuilderInterface;

    /**
     * Set tracking status
     *
     * @param string $status
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setStatus(string $status): TrackingPayloadBuilderInterface;

    /**
     * Set tracker ID (used for update/cancel operations and URL construction)
     *
     * @param string $trackerId
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setTrackerId(string $trackerId): TrackingPayloadBuilderInterface;

    /**
     * Set carrier module data
     *
     * @param string $name
     * @param string $version
     * @param string $deliveryOption
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setCarrierModule(string $name, string $version, string $deliveryOption): TrackingPayloadBuilderInterface;

    /**
     * Set carrier data from Carrier object with automatic logic
     *
     * @param \Carrier $carrier
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setCarrierFromCarrierObject(\Carrier $carrier): TrackingPayloadBuilderInterface;

    /**
     * Reset builder to clean state
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function reset(): TrackingPayloadBuilderInterface;

    /**
     * Set order context for language and shop
     *
     * @param int $languageId
     * @param int $shopId
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setOrderContext(int $languageId, int $shopId): TrackingPayloadBuilderInterface;

    /**
     * Set delivery address
     *
     * @param array $address
     *
     * @return TrackingPayloadBuilderInterface
     */
    public function setAddress(array $address): TrackingPayloadBuilderInterface;
}
