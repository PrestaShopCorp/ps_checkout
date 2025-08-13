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

interface TrackingBaseNodeBuilderInterface
{
    /**
     * Set order ID
     *
     * @param string $orderId
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setOrderId(string $orderId): TrackingBaseNodeBuilderInterface;

    /**
     * Set capture ID
     *
     * @param string $captureId
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setCaptureId(string $captureId): TrackingBaseNodeBuilderInterface;

    /**
     * Set tracking number
     *
     * @param string $trackingNumber
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setTrackingNumber(string $trackingNumber): TrackingBaseNodeBuilderInterface;

    /**
     * Set carrier
     *
     * @param string $carrier
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setCarrier(string $carrier): TrackingBaseNodeBuilderInterface;

    /**
     * Set notify payer
     *
     * @param bool $notifyPayer
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setNotifyPayer(bool $notifyPayer): TrackingBaseNodeBuilderInterface;

    /**
     * Set tracking status
     *
     * @param string $status
     *
     * @return TrackingBaseNodeBuilderInterface
     */
    public function setStatus(string $status): TrackingBaseNodeBuilderInterface;

    /**
     * Build the base tracking payload
     *
     * @return array
     */
    public function build(): array;
}
