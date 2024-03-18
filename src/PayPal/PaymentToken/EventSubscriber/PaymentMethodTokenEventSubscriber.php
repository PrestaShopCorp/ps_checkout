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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command\DeletePaymentTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command\SavePaymentTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletionInitiatedEvent;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentMethodTokenEventSubscriber implements EventSubscriberInterface
{
    /** @var Ps_checkout */
    private $module;

    /** @var CommandBusInterface */
    private $commandBus;
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;

    public function __construct(Ps_checkout $module, PayPalOrderRepository $payPalOrderRepository)
    {
        $this->module = $module;
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentTokenCreatedEvent::class => [
                ['saveCreatedPaymentMethodToken'],
            ],
            PaymentTokenDeletedEvent::class => [
                ['deletePaymentMethodToken'],
            ],
            PaymentTokenDeletionInitiatedEvent::class => [
                [''], // No sé
            ],
        ];
    }

    public function saveCreatedPaymentMethodToken(PaymentTokenCreatedEvent $event)
    {
        $resource = $event->getResource();
        $orderId = isset($resource['metadata']['order_id']) ? $resource['metadata']['order_id'] : null;
        $setFavorite = false;

        if ($orderId) {
            try {
                $order = $this->payPalOrderRepository->getPayPalOrderById($orderId);
                $setFavorite = strpos($order->getCustomerIntent(), PayPalOrder::CUSTOMER_INTENT_FAVORITE) !== false;
            } catch (\Exception $exception) {
            }
        }

        $this->commandBus->handle(new SavePaymentTokenCommand(
            $resource['id'],
            $resource['customer']['id'],
            array_keys($resource['payment_source'])[0],
            $resource,
            $event->getMerchantId(),
            $setFavorite
        ));
    }

    public function deletePaymentMethodToken(PaymentTokenDeletedEvent $event)
    {
        $this->commandBus->handle(new DeletePaymentTokenCommand(
            $event->getResource()['id']
        ));
    }
}
