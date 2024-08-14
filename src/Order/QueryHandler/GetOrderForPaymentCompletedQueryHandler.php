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

namespace PrestaShop\Module\PrestashopCheckout\Order\QueryHandler;

use Order;
use OrderPayment;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopCollection;
use PrestaShopDatabaseException;
use PrestaShopException;
use Validate;

class GetOrderForPaymentCompletedQueryHandler
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    public function __construct(PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @param GetOrderForPaymentCompletedQuery $query
     *
     * @return GetOrderForPaymentCompletedQueryResult
     *
     * @throws PsCheckoutException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handle(GetOrderForPaymentCompletedQuery $query)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($query->getOrderPayPalId()->getValue());

        if (!$psCheckoutCart) {
            throw new CartNotFoundException('No PrestaShop Cart associated to this PayPal Order at this time.');
        }

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', $psCheckoutCart->getIdCart());

        if (!$orders->count()) {
            throw new OrderNotFoundException('No PrestaShop Order associated to this PayPal Order at this time.');
        }

        /** @var Order $order */
        $order = $orders->getFirst();

        if (!Validate::isLoadedObject($order)) {
            throw new OrderNotFoundException('No PrestaShop Order associated to this PayPal Order at this time.');
        }

        /** @var OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();
        $orderPaymentId = null;

        if (!empty($orderPayments)) {
            foreach ($orderPayments as $orderPayment) {
                if ($orderPayment->transaction_id === $query->getCapturePayPalId()->getValue()) {
                    $orderPaymentId = (int) $orderPayment->id;
                }
            }
        }

        return new GetOrderForPaymentCompletedQueryResult(
            (int) $order->id,
            (int) $order->id_cart,
            (bool) $order->hasBeenPaid(),
            (string) $order->total_paid,
            (int) $order->id_currency,
            $psCheckoutCart->getPaypalFundingSource(),
            $orderPaymentId
        );
    }
}
