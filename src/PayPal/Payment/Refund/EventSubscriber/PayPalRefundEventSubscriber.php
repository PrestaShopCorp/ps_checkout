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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Event\PayPalRefundEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use Ps_checkout;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalRefundEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var CheckOrderAmount
     */
    private $checkOrderAmount;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var CacheInterface
     */
    private $capturePayPalCache;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @var OrderStateMapper
     */
    private $orderStateMapper;
    /**
     * @var PayPalOrderProvider
     */
    private $orderProvider;

    public function __construct(
        Ps_checkout $module,
        CheckOrderAmount $checkOrderAmount,
        CacheInterface $capturePayPalCache,
        CacheInterface $orderPayPalCache,
        OrderStateMapper $orderStateMapper,
        PayPalOrderProvider $orderProvider
    ) {
        $this->module = $module;
        $this->checkOrderAmount = $checkOrderAmount;
        $this->commandBus = $this->module->getService('ps_checkout.bus.command');
        $this->capturePayPalCache = $capturePayPalCache;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->orderStateMapper = $orderStateMapper;
        $this->orderProvider = $orderProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureRefundedEvent::class => [
                ['setPaymentRefundedOrderStatus'],
                ['updateCache'],
            ],
        ];
    }

    public function setPaymentRefundedOrderStatus(PayPalCaptureRefundedEvent $event)
    {
        try {
            /** @var GetOrderForPaymentRefundedQueryResult $order */
            $order = $this->commandBus->handle(new GetOrderForPaymentRefundedQuery($event->getPayPalOrderId()->getValue()));
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($this->orderPayPalCache->has($event->getPayPalOrderId()->getValue())) {
            $this->orderPayPalCache->delete($event->getPayPalOrderId()->getValue());
        }

        if (!$order->hasBeenPaid() || $order->hasBeenTotallyRefund()) {
            return;
        }

        $orderPayPal = $this->orderProvider->getById($event->getPayPalOrderId()->getValue());

        if (empty($orderPayPal['purchase_units'][0]['payments']['refunds'])) {
            return;
        }

        $totalRefunded = array_reduce($orderPayPal['purchase_units'][0]['payments']['refunds'], function ($totalRefunded, $refund) {
            return $totalRefunded + (float) $refund['amount']['value'];
        });

        $orderFullyRefunded = (float) $order->getTotalAmount() <= (float) $totalRefunded;

        $this->commandBus->handle(
            new UpdateOrderStatusCommand(
                $order->getOrderId()->getValue(),
                $this->orderStateMapper->getIdByKey($orderFullyRefunded ? OrderStateConfigurationKeys::PS_CHECKOUT_STATE_REFUNDED : OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED)
            )
        );
    }

    public function updateCache(PayPalRefundEvent $event)
    {
        if ($this->orderPayPalCache->has($event->getPayPalOrderId()->getValue())) {
            $this->orderPayPalCache->delete($event->getPayPalOrderId()->getValue());
        }
    }
}
