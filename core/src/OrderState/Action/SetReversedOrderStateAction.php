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

namespace PsCheckout\Core\OrderState\Action;

use Order;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;

class SetReversedOrderStateAction implements SetOrderStateActionInterface
{
    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var OrderStateMapperInterface
     */
    private $orderStateMapper;

    /**
     * @var ChangeOrderStateActionInterface
     */
    private $changeOrderStateAction;

    public function __construct(
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderRepositoryInterface $orderRepository,
        ConfigurationInterface $configuration,
        OrderStateMapperInterface $orderStateMapper,
        ChangeOrderStateActionInterface $changeOrderStateAction
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->configuration = $configuration;
        $this->orderStateMapper = $orderStateMapper;
        $this->changeOrderStateAction = $changeOrderStateAction;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(string $payPalOrderId)
    {
        $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrderId]);

        if (!$payPalOrder) {
            throw new PsCheckoutException('PayPal order not found.', PsCheckoutException::ORDER_NOT_FOUND);
        }

        $order = $this->orderRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);

        if (
            !$this->hasBeenPaid($order)
            || $this->hasBeenRefunded($order)
        ) {
            return;
        }

        $this->changeOrderStateAction->execute(
            $order->id,
            $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR)
        );
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    private function hasBeenPaid(Order $order): bool
    {
        $hasBeenPaid = $order->hasBeenPaid();

        $hasBeenCompleted = count(
            $order->getHistory(
                $order->id_lang,
                $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED)
            )
        );

        $hasBeenPartiallyPaid = count(
            $order->getHistory(
                $order->id_lang,
                $this->configuration->getInteger(OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID)
            )
        );

        return $hasBeenPaid || $hasBeenCompleted || $hasBeenPartiallyPaid;
    }

    /**
     * @param Order $order
     *
     * @return bool
     */
    private function hasBeenRefunded(Order $order): bool
    {
        return (bool) count(
            $order->getHistory(
                (new Order($order->id))->id_lang,
                $this->configuration->get(OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED)
            )
        );
    }
}
