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
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForPaymentCompletedQueryResult;
use PrestaShopDatabaseException;
use PrestaShopException;

class GetOrderForPaymentCompletedQueryHandler
{
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
        $module = \Module::getInstanceByName('ps_checkout');
        $module->getLogger()->debug(
            __CLASS__,
            [
                'query' => $query,
                'id_cart' => $query->getCartId()->getValue(),
            ]
        );

        $orderId = null;

        // Order::getIdByCartId() is available since PrestaShop 1.7.1.0
        if (method_exists(\Order::class, 'getIdByCartId')) {
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getIdByCartId($query->getCartId()->getValue());
        } elseif (method_exists(\Order::class, 'getOrderByCartId')) { // Order::getIdByCartId() is available before PrestaShop 1.7.1.0, removed since PrestaShop 8.0.0
            // @phpstan-ignore-next-line
            $orderId = (int) \Order::getOrderByCartId($query->getCartId()->getValue());
        }
        $module->getLogger()->debug('!!!!', [$orderId]);
        if (!$orderId) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.', PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        // $order = $this->cache->get($query->getCartId()->getValue());
        $order = new \Order($orderId);

        if (!\Validate::isLoadedObject($order)) {
            throw new PsCheckoutException('No PrestaShop Order associated to this PayPal Order at this time.', PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        return new GetOrderForPaymentCompletedQueryResult(
            (int) $order->id,
            (int) $order->getCurrentState(),
            (bool) $order->hasBeenPaid(),
            (string) $order->getTotalProductsWithTaxes(),
            (string) $order->getTotalPaid(),
            (int) $order->id_currency
        );
    }
}
