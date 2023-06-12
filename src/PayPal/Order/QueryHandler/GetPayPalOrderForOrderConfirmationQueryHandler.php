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

use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheSettings;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderFetchedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQueryResult;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class GetPayPalOrderForOrderConfirmationQueryHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CacheInterface $orderPayPalCache
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, CacheInterface $orderPayPalCache)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->orderPayPalCache = $orderPayPalCache;
    }

    /**
     * @param GetPayPalOrderForOrderConfirmationQuery $query
     *
     * @return GetPayPalOrderForOrderConfirmationQueryResult
     *
     * @throws PayPalOrderException
     * @throws InvalidArgumentException
     */
    public function handle(GetPayPalOrderForOrderConfirmationQuery $query)
    {
        /** @var array{id: string, status: string} $order */
        $order = $this->orderPayPalCache->get(CacheSettings::PAYPAL_ORDER_ID . $query->getOrderId()->getValue());

        if (!empty($order) && ($order['status'] === 'PENDING' || $order['status'] === 'COMPLETED')) {
            return new GetPayPalOrderForOrderConfirmationQueryResult($order);
        }

        try {
            $orderPayPal = new PaypalOrder($query->getOrderId()->getValue());
        } catch (\Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PayPal Order %s', $query->getOrderId()->getValue()), PayPalOrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if (!$orderPayPal->isLoaded()) {
            throw new PayPalOrderException(sprintf('No data for PayPal Order %s', $query->getOrderId()->getValue()), PayPalOrderException::EMPTY_ORDER_DATA);
        }

        $this->eventDispatcher->dispatch(
            new PayPalOrderFetchedEvent($query->getOrderId()->getValue(), $orderPayPal->getOrder())
        );

        return new GetPayPalOrderForOrderConfirmationQueryResult($orderPayPal->getOrder());
    }
}
