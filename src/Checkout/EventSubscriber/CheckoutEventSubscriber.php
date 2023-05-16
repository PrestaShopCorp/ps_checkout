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

namespace PrestaShop\Module\PrestashopCheckout\Checkout\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Checkout\Event\CheckoutCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\Checkout\Exception\CheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\RemovePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePaymentMethodSelectedCommand;
use PrestaShop\Module\PrestashopCheckout\Session\Exception\PsCheckoutSessionException;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @param Ps_checkout $module
     */
    public function __construct(Ps_checkout $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutCompletedEvent::class => [
                ['removePayPalOrderCache'],
                ['updatePaymentMethodSelected'],
                ['fetchPayPalOrder'],
            ],
        ];
    }

    /**
     * Remove the order already in cache to ensure fetching the order with updated status
     *
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     *
     * @throws CheckoutException
     */
    public function removePayPalOrderCache(CheckoutCompletedEvent $event)
    {
        $this->module->getService('ps_checkout.bus.command')->handle(new RemovePayPalOrderCacheCommand(
            $event->getPayPalOrderId()->getValue()
        ));
    }

    /**
     * Update the payment method selected
     * - funding source name
     * - isHostedFields
     * - isExpressCheckout
     *
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     *
     * @throws CartException
     * @throws PayPalOrderException
     * @throws PsCheckoutSessionException
     */
    public function updatePaymentMethodSelected(CheckoutCompletedEvent $event)
    {
        $this->module->getService('ps_checkout.bus.command')->handle(new UpdatePaymentMethodSelectedCommand(
            $event->getCartId()->getValue(),
            $event->getPayPalOrderId()->getValue(),
            $event->getFundingSource(),
            $event->isHostedFields(),
            $event->isExpressCheckout()
        ));
    }

    /**
     * Fetch PayPal Order from API
     *
     * @param CheckoutCompletedEvent $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function fetchPayPalOrder(CheckoutCompletedEvent $event)
    {
        $this->module->getService('ps_checkout.bus.command')->handle(new GetPayPalOrderQuery(
            $event->getPayPalOrderId()->getValue()
        ));
    }
}
