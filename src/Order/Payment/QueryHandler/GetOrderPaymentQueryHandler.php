<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Order\Payment\Exception\OrderPaymentException;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Payment\Query\GetOrderPaymentQueryResult;

class GetOrderPaymentQueryHandler
{
    /**
     * @param GetOrderPaymentQuery $query
     *
     * @return GetOrderPaymentQueryResult
     *
     * @throws \PrestaShopException
     * @throws OrderPaymentException
     */
    public function handle(GetOrderPaymentQuery $query)
    {
        $orderPaymentCollection = \ObjectModel::hydrateCollection(
            'OrderPayment',
            Db::getInstance()->executeS(
                'SELECT *
			    FROM `' . _DB_PREFIX_ . 'order_payment`
			    WHERE `transaction_id` = \'' . pSQL($query->getTransactionId()->getValue()) . '\''
            )
        );

        if (empty($orderPaymentCollection)) {
            throw new OrderPaymentException('No PrestaShop OrderPayment associated to this PayPal capture id at this time.', OrderPaymentException::INVALID_ID);
        }

        /** @var \OrderPayment $orderPayment */
        $orderPayment = end($orderPaymentCollection);

        return new GetOrderPaymentQueryResult(
            $orderPayment->transaction_id,
            $orderPayment->order_reference,
            $orderPayment->amount,
            $orderPayment->payment_method,
            $orderPayment->date_add
        );
    }
}
