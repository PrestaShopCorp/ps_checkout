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

use Cart;
use Exception;
use Module;
use Order;
use PaymentModule;
use PrestaShop\Module\PrestashopCheckout\Context\ContextStateManager;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShopDatabaseException;
use PrestaShopException;

class CreateOrderCommandHandler extends AbstractOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContextStateManager
     */
    private $contextStateManager;

    /**
     * @param ContextStateManager $contextStateManager
     */
    public function __construct(ContextStateManager $contextStateManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->contextStateManager = $contextStateManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CreateOrderCommand $command
     *
     * @throws OrderException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handle(CreateOrderCommand $command)
    {
        /** @var PaymentModule|false $paymentModule */
        $paymentModule = Module::getInstanceByName($command->getPaymentModuleName());

        if (false === $paymentModule) {
            throw new OrderException(sprintf('Unable to get "%s" module instance.', $command->getPaymentModuleName()), OrderException::MODULE_INSTANCE_NOT_FOUND);
        }

        $cart = new Cart($command->getCartId()->getValue());
        $this->setCartContext($this->contextStateManager, $cart);

        $extraVars = [];

        // Transaction identifier is needed only when an OrderPayment will be created
        // It requires a positive paid amount and an OrderState that's consider the associated order as validated.
        if ($command->getPaidAmount() && $command->getTransactionId()) {
            $extraVars['transaction_id'] = $command->getTransactionId();
        }

        try {
            $paymentModule->validateOrder(
                (int) $cart->id,
                $command->getOrderStateId(),
                $command->getPaidAmount(),
                $command->getPaymentMethod(),
                null,
                $extraVars,
                null,
                false,
                $cart->secure_key
            );
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', var_export($cart->id, true)), OrderException::FAILED_ADD_ORDER, $exception);
        }

        if (!$cart->orderExists()) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', var_export($cart->id, true)), OrderException::ORDER_NOT_FOUND);
        }

        // It happens this returns null in case of override or weird modules
        if ($paymentModule->currentOrder) {
            $this->eventDispatcher->dispatch(new OrderCreatedEvent((int) $paymentModule->currentOrder, (int) $cart->id));

            return;
        }

        // Order::getIdByCartId() is available since PrestaShop 1.7.1.0
        if (method_exists(Order::class, 'getIdByCartId')) {
            // @phpstan-ignore-next-line
            $this->eventDispatcher->dispatch(new OrderCreatedEvent((int) Order::getIdByCartId($cart->id), (int) $cart->id));

            return;
        }

        // Order::getIdByCartId() is available before PrestaShop 1.7.1.0, removed since PrestaShop 8.0.0
        if (method_exists(Order::class, 'getOrderByCartId')) {
            // @phpstan-ignore-next-line
            $this->eventDispatcher->dispatch(new OrderCreatedEvent((int) Order::getOrderByCartId($cart->id), (int) $cart->id));

            return;
        }

        throw new OrderException(sprintf('Unable to retrieve order identifier from Cart #%s.', var_export($cart->id, true)), OrderException::ORDER_NOT_FOUND);
    }
}
