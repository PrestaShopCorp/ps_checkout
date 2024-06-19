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
use OrderSlip;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentRefundedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopCollection;
use PrestaShopDatabaseException;
use PrestaShopException;
use PsCheckoutCart;
use Validate;

class GetOrderForPaymentRefundedQueryHandler
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
     * @param GetOrderForPaymentRefundedQuery $query
     *
     * @return GetOrderForPaymentRefundedQueryResult
     *
     * @throws PsCheckoutException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handle(GetOrderForPaymentRefundedQuery $query)
    {
        /** @var PsCheckoutCart|false $psCheckoutCart */
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

        $totalRefund = $this->getTotalRefund($order);

        return new GetOrderForPaymentRefundedQueryResult(
            (int) $order->id,
            (int) $order->getCurrentState(),
            (bool) $order->hasBeenPaid(),
            (bool) count($order->getHistory((int) $order->id_lang, \Configuration::get('PS_CHECKOUT_STATE_PARTIALLY_REFUNDED'))),
            (bool) count($order->getHistory((int) $order->id_lang, \Configuration::get('PS_CHECKOUT_STATE_REFUNDED'))),
            (string) $order->getTotalPaid(),
            (string) $totalRefund,
            (int) $order->id_currency
        );
    }

    private function getTotalRefund(Order $order)
    {
        /** @var OrderSlip[] $orderSlips */
        $orderSlips = $order->getOrderSlipsCollection()->getResults();
        $refundAmount = 0;

        foreach ($orderSlips as $orderSlip) {
            $refundAmount += $orderSlip->amount + $orderSlip->shipping_cost_amount;
        }

        return $refundAmount;
    }
}
