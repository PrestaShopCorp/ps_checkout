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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheSettings;
use PrestaShopDatabaseException;
use PrestaShopException;
use Psr\SimpleCache\CacheInterface;

class GetOrderForPaymentCompletedQueryHandler
{
    /**
     * @var CacheInterface
     */
    private $orderPrestaShopcache;

    /**
     * @param CacheInterface $orderPrestaShopcache
     */
    public function __construct(CacheInterface $orderPrestaShopcache)
    {
        $this->orderPrestaShopcache = $orderPrestaShopcache;
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
        /** @var GetOrderForPaymentCompletedQueryResult $result */
        $result = $this->orderPrestaShopcache->get(CacheSettings::CART_ID . $query->getCartId()->getValue());
        if (!empty($result) && $result instanceof GetOrderForPaymentCompletedQueryResult) {
            return $result;
        }

        $orderId = null;
        // Order::getIdByCartId() is available since PrestaShop 1.7.1.0
        if (method_exists(\Order::class, 'getIdByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getIdByCartId($query->getCartId()->getValue());
        } elseif (method_exists(\Order::class, 'getOrderByCartId')) { // Order::getIdByCartId() is available before PrestaShop 1.7.1.0, removed since PrestaShop 8.0.0
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getOrderByCartId($query->getCartId()->getValue());
        }

        if (!$orderId) {
            throw new OrderNotFoundException('No PrestaShop Order associated to this PayPal Order at this time.', OrderNotFoundException::NOT_FOUND);
        }

        $order = new \Order($orderId);

        if (!\Validate::isLoadedObject($order)) {
            throw new OrderNotFoundException('No PrestaShop Order associated to this PayPal Order at this time.', OrderNotFoundException::NOT_FOUND);
        }
        $result = new GetOrderForPaymentCompletedQueryResult(
            (int) $order->id,
            (int) $order->getCurrentState(),
            (bool) $order->hasBeenPaid(),
            (string) $order->getTotalProductsWithTaxes(),
            (string) $order->getTotalPaid(),
            (int) $order->id_currency
        );

        $this->orderPrestaShopcache->set(CacheSettings::CART_ID . $query->getCartId()->getValue(), $result);

        return $result;
    }
}
