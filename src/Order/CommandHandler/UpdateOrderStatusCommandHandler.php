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

namespace PrestaShop\Module\PrestashopCheckout\Order\CommandHandler;

use Exception;
use OrderHistory;
use OrderState;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderStatusUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckOrderState;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;

class UpdateOrderStatusCommandHandler extends AbstractOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CheckOrderState
     */
    private $checkOrderState;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, CheckOrderState $checkOrderState)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->checkOrderState = $checkOrderState;
    }

    /**
     * @param UpdateOrderStatusCommand $command
     *
     * @return void
     *
     * @throws OrderException
     */
    public function handle(UpdateOrderStatusCommand $command)
    {
        $order = $this->getOrder($command->getOrderId());
        $orderState = $this->getOrderStateObject($command->getNewOrderStatusId());

        if ($order->getCurrentState() == $orderState->id) {
            throw new OrderException(sprintf('The order #%d has already been assigned to OrderState #%d', $command->getOrderId()->getValue(), $orderState->id), OrderException::ORDER_HAS_ALREADY_THIS_STATUS);
        }

        if (!$this->checkOrderState->isOrderStateTransitionAvailable($order->getCurrentState(), $orderState->id)) {
            throw new OrderException(sprintf('Transition is not valid for order #%d Current order state %d, new order state %d', $command->getOrderId()->getValue(), $order->getCurrentState(), $orderState->id), OrderException::TRANSITION_NOT_ALLOWED);
        }

        // Create new OrderHistory
        $history = new OrderHistory();
        $history->id_order = $order->id;

        $useExistingPayments = false;
        if (!$order->hasInvoice()) {
            $useExistingPayments = true;
        }

        try {
            $history->changeIdOrderState((int) $orderState->id, $order, $useExistingPayments);
            // Save all changes
            $historyAdded = $history->addWithemail(true);
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to update status or sent email when changing OrderState #%d of Order #%d.', $command->getNewOrderStatusId()->getValue(), $command->getOrderId()->getValue()), OrderException::FAILED_UPDATE_ORDER_STATUS, $exception);
        }

        if (!$historyAdded) {
            throw new OrderException(sprintf('Failed to update status or sent email when changing OrderState #%d of Order #%d.', $command->getNewOrderStatusId()->getValue(), $command->getOrderId()->getValue()), OrderException::FAILED_UPDATE_ORDER_STATUS);
        }

        $this->eventDispatcher->dispatch(new OrderStatusUpdatedEvent((int) $orderState->id));
    }

    /**
     * @param OrderStateId $orderStatusId
     *
     * @return OrderState
     *
     * @throws OrderException
     */
    private function getOrderStateObject(OrderStateId $orderStatusId)
    {
        try {
            $orderState = new OrderState($orderStatusId->getValue());
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Unable to retrieve OrderState #%d', $orderStatusId->getValue()), OrderException::ORDER_STATUS_NOT_FOUND, $exception);
        }

        if ($orderState->id !== $orderStatusId->getValue()) {
            throw new OrderException(sprintf('Unable to found OrderState #%d', $orderStatusId->getValue()), OrderException::ORDER_STATUS_NOT_FOUND);
        }

        return $orderState;
    }
}