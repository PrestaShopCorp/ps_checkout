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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\CommandHandler;

use Currency;
use OrderInvoice;
use OrderPayment;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\AbstractOrderHandler;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStateCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderPaymentCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use Validate;

class UpdateOrderStateCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param UpdateOrderStateCommand $command
     *
     * @throws OrderException
     */
    public function handle(UpdateOrderStateCommand $command)
    {
        $orderPS = new \Order($command->getOrderId()->getValue());
        $currentOrderStateId = (int) $orderPS->getCurrentState();
        if ($currentOrderStateId !== $command->getNewOrderStateId()->getValue()) {
            $orderHistory = new \OrderHistory();
            $orderHistory->id_order = $command->getOrderId()->getValue();
            try {
                $orderHistory->changeIdOrderState($command->getNewOrderStateId()->getValue(), $command->getOrderId()->getValue());
                $orderHistory->addWithemail();
            } catch (\ErrorException $exception) {
                // Notice or warning from PHP
                // For example : https://github.com/PrestaShop/PrestaShop/issues/18837
            } catch (\Exception $exception) {
                throw new PsCheckoutException('Unable to change PrestaShop OrderState', PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR, $exception);
            }
        }

        $this->eventDispatcher->dispatch(new UpdatedOrderStateEvent($command->getOrderId()->getValue(),$command->getNewOrderStateId()->getValue()));
    }
}
