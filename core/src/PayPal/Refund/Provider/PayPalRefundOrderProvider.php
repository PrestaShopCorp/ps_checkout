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

namespace PsCheckout\Core\PayPal\Refund\Provider;

use Order;
use PrestaShopCollection;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Refund\ValueObject\PayPalRefundOrder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class PayPalRefundOrderProvider implements PayPalRefundOrderProviderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    public function __construct(
        ConfigurationInterface $configuration,
        PayPalOrderRepositoryInterface $payPalOrderRepository
    ) {
        $this->configuration = $configuration;
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function provide(string $payPalOrderId): PayPalRefundOrder
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderId]);

        if (!$payPalOrder) {
            throw new OrderException('PayPal Order not found.', OrderException::ORDER_NOT_FOUND);
        }

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', $payPalOrder->getIdCart());

        /** @var Order $order */
        $order = $orders->getFirst();

        if (!$order || !$order->id) {
            throw new OrderException('No PrestaShop Order associated to this PayPal Order at this time.', OrderException::ORDER_NOT_FOUND);
        }

        $hasBeenPaid = $order->hasBeenPaid();
        $hasBeenCompleted = count($order->getHistory($order->id_lang, $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED)));
        $hasBeenPartiallyPaid = count($order->getHistory($order->id_lang, $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID)));

        return new PayPalRefundOrder(
            (int) $order->id,
            (int) $order->getCurrentState(),
            $hasBeenPaid || $hasBeenCompleted || $hasBeenPartiallyPaid,
            (bool) count($order->getHistory((int) $order->id_lang, $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_REFUNDED))),
            (bool) count($order->getHistory((int) $order->id_lang, $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED))),
            (string) $order->getTotalPaid(),
            (int) $order->id_currency
        );
    }
}
