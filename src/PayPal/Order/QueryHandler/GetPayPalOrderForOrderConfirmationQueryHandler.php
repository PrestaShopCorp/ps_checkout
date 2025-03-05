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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQueryResult;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use Symfony\Component\Cache\Adapter\ChainAdapter;

class GetPayPalOrderForOrderConfirmationQueryHandler
{
    public function __construct(private ChainAdapter $orderPayPalCache)
    {
    }

    public function __invoke(GetPayPalOrderForOrderConfirmationQuery $query)
    {
        /** @var array{id: string, status: string} $order */
        $order = $this->orderPayPalCache->get($query->getOrderPayPalId()->getValue(), function () use ($query) {
            return (new PaypalOrder($query->getOrderPayPalId()->getValue()))->getOrder();
        });

        if ($order['status'] === 'PENDING' || $order['status'] === 'COMPLETED') {
            return new GetPayPalOrderForOrderConfirmationQueryResult($order);
        }

        try {
            $orderPayPal = new PaypalOrder($query->getOrderPayPalId()->getValue());
        } catch (\Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PayPal Order %s', $query->getOrderPayPalId()->getValue()), PayPalOrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if (!$orderPayPal->isLoaded()) {
            throw new PayPalOrderException(sprintf('No data for PayPal Order %s', $query->getOrderPayPalId()->getValue()), PayPalOrderException::EMPTY_ORDER_DATA);
        }

        return new GetPayPalOrderForOrderConfirmationQueryResult($orderPayPal->getOrder());
    }
}
