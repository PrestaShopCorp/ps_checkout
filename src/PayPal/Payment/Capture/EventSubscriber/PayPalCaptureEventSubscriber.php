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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureCompletedEvent::NAME => 'onPayPalCaptureCompleted',
            PayPalCaptureDeniedEvent::NAME => 'onPayPalCaptureDenied',
            PayPalCaptureRefundedEvent::NAME => 'onPayPalCaptureRefunded',
        ];
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureCompleted(PayPalCaptureCompletedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if an Order on PrestaShop already exist
        // Check if the OrderState of Order on PrestaShop need to be updated
    }

    /**
     * @param PayPalCaptureDeniedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureDenied(PayPalCaptureDeniedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if an Order on PrestaShop already exist
        // Check if the OrderState of Order on PrestaShop need to be updated
    }

    /**
     * @param PayPalCaptureRefundedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureRefunded(PayPalCaptureRefundedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if an Order on PrestaShop already exist
        // Check if the OrderState of Order on PrestaShop need to be updated
        // Check if refund has been executed on PrestaShop
    }
}
