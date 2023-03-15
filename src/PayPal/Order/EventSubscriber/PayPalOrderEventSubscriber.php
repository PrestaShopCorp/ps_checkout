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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::NAME => 'onPayPalOrderCreated',
            PayPalOrderApprovedEvent::NAME => 'onPayPalOrderApproved',
            PayPalOrderCompletedEvent::NAME => 'onPayPalOrderCompleted',
        ];
    }

    /**
     * @param PayPalOrderCreatedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderCreated(PayPalOrderCreatedEvent $event)
    {
        // Update data on pscheckout_cart table
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderApproved(PayPalOrderApprovedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if Cart is still valid
        // Check if an Order on PrestaShop already exist
        // Create an Order on PrestaShop if needed
        // Proceed to Capture
    }

    /**
     * @param PayPalOrderCompletedEvent $event
     *
     * @return void
     */
    public function onPayPalOrderCompleted(PayPalOrderCompletedEvent $event)
    {
        // Update data on pscheckout_cart table
        // Check if an Order on PrestaShop already exist
        // Check if the OrderState of Order on PrestaShop need to be updated
    }
}
