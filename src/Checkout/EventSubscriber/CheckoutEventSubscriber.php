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
use PrestaShop\Module\PrestashopCheckout\Checkout\CheckoutChecker;
use PrestaShop\Module\PrestashopCheckout\Checkout\Event\CheckoutCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePaymentMethodSelectedCommand;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CheckoutChecker
     */
    private $checkoutChecker;

    /**
     * @param Ps_checkout $module
     */
    public function __construct(Ps_checkout $module)
    {
        $this->module = $module;
        $this->checkoutChecker = $this->module->getService('ps_checkout.checkout.checker');
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CheckoutCompletedEvent::class => [
                ['updatePaymentMethodSelected'],
                ['proceedToPayment'],
            ],
        ];
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
     */
    public function updatePaymentMethodSelected(CheckoutCompletedEvent $event)
    {
        $this->commandBus->handle(new UpdatePaymentMethodSelectedCommand(
            $event->getCartId()->getValue(),
            $event->getPayPalOrderId()->getValue(),
            $event->getFundingSource(),
            $event->isExpressCheckout(),
            $event->isHostedFields()
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
     * @throws PsCheckoutException
     */
    public function proceedToPayment(CheckoutCompletedEvent $event)
    {
        /** @var GetPayPalOrderForCheckoutCompletedQueryResult $getPayPalOrderForCheckoutCompletedQueryResult */
        $getPayPalOrderForCheckoutCompletedQueryResult = $this->module->getService('ps_checkout.bus.command')->handle(new GetPayPalOrderForCheckoutCompletedQuery(
            $event->getPayPalOrderId()->getValue()
        ));

        $this->checkoutChecker->continueWithAuthorization($event->getCartId()->getValue(), $getPayPalOrderForCheckoutCompletedQueryResult->getPayPalOrder());

        $this->commandBus->handle(
            new CapturePayPalOrderCommand(
                $event->getPayPalOrderId()->getValue(),
                $event->getFundingSource()
            )
        );
    }
}
