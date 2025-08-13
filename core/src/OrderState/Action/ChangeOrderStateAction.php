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
use OrderState;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Infrastructure\Repository\OrderHistoryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderStateRepositoryInterface;

class ChangeOrderStateAction implements ChangeOrderStateActionInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderStateRepositoryInterface
     */
    private $orderStateRepository;

    /**
     * @var OrderHistoryRepositoryInterface
     */
    private $orderHistoryRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderStateRepositoryInterface $orderStateRepository
     * @param OrderHistoryRepositoryInterface $orderHistoryRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderStateRepositoryInterface $orderStateRepository,
        OrderHistoryRepositoryInterface $orderHistoryRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderStateRepository = $orderStateRepository;
        $this->orderHistoryRepository = $orderHistoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(int $orderId, string $newOrderStateId)
    {
        /** @var Order $order */
        $order = $this->orderRepository->getOneBy(['id_order' => $orderId]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(['id_order_state' => $newOrderStateId]);

        if ($order->getCurrentState() === $newOrderState->id) {
            throw new OrderException(sprintf('The order #%d has already been assigned to OrderState #%d', $orderId, $newOrderState->id), OrderException::ORDER_HAS_ALREADY_THIS_STATUS);
        }

        $this->orderHistoryRepository->create($newOrderState->id, $order->id, !$order->hasInvoice());
    }
}
