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

use Currency;
use OrderInvoice;
use OrderPayment;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\AbstractOrderHandler;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderPaymentCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use Validate;

class AddOrderPaymentHandler extends AbstractOrderHandler
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
     * @param AddOrderPaymentCommand $command
     *
     * @throws OrderException
     */
    public function handle(AddOrderPaymentCommand $command)
    {
        $order = $this->getOrder($command->getOrderId());
        $currency = Currency::getCurrencyInstance($command->getPaymentCurrencyId());

        if (!Validate::isLoadedObject($currency)) {
            throw new OrderException('The selected currency is invalid.', OrderException::INVALID_CURRENCY);
        }

        $orderHasInvoice = $order->hasInvoice();
        /** @var OrderInvoice|null $orderInvoice */
        $orderInvoice = $orderHasInvoice ? $order->getNotPaidInvoicesCollection()->getFirst() : null;

        if ($orderHasInvoice && !Validate::isLoadedObject($orderInvoice)) {
            throw new OrderException('The invoice is invalid.', OrderException::INVALID_INVOICE);
        }

        $paymentAdded = $order->addOrderPayment(
            $command->getPaymentAmount(),
            $command->getPaymentMethod(),
            $command->getPaymentTransactionId(),
            $currency,
            $command->getPaymentDate()->format('Y-m-d H:i:s'),
            $orderInvoice
        );

        if (!$paymentAdded) {
            throw new OrderException(sprintf('Failed to add a payment to Order #%s.', $command->getOrderId()->getValue()), OrderException::FAILED_ADD_PAYMENT);
        }

        /** @var OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPayments();
        $latestOrderPayment = end($orderPayments);

        $this->eventDispatcher->dispatch(new OrderPaymentCreatedEvent((int) $latestOrderPayment->id));
    }
}
