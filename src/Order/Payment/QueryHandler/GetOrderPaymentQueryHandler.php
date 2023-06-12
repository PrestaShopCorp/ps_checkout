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

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\QueryHandler;

use Order;
use OrderPayment;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Exception\OrderPaymentException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQueryResult;
use PrestaShopException;

class GetOrderPaymentQueryHandler
{
    /**
     * @param GetOrderPaymentQuery $query
     *
     * @return GetOrderPaymentQueryResult
     *
     * @throws PrestaShopException
     * @throws OrderPaymentException
     */
    public function handle(GetOrderPaymentQuery $query)
    {
        $order = new Order($query->getOrderId()->getValue());
        /** @var OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();

        if (empty($orderPayments)) {
            throw new OrderPaymentException('No PrestaShop OrderPayment associated to this PayPal capture id at this time.', OrderPaymentException::INVALID_ID);
        }

        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment->transaction_id === $query->getTransactionId()->getValue()) {
                return new GetOrderPaymentQueryResult(
                    $orderPayment->transaction_id,
                    $orderPayment->order_reference,
                    $orderPayment->amount,
                    $orderPayment->payment_method,
                    $orderPayment->date_add
                );
            }
        }

        throw new OrderPaymentException('No PrestaShop OrderPayment associated to this PayPal capture id at this time.', OrderPaymentException::INVALID_ID);
    }
}
