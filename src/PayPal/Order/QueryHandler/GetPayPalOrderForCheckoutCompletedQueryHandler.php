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

use Exception;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheSettings;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderFetchedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderEventDispatcher;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCheckoutCompletedQueryResult;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * We need to know if the Order Status is APPROVED and in case of Card payment if 3D Secure allow to capture
 */
class GetPayPalOrderForCheckoutCompletedQueryHandler
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
     * @var PayPalOrderEventDispatcher
     */
    private $paypalOrderEventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CacheInterface $orderPayPalCache
     * @param PayPalOrderEventDispatcher $paypalOrderEventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, CacheInterface $orderPayPalCache, PayPalOrderEventDispatcher $paypalOrderEventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->paypalOrderEventDispatcher = $paypalOrderEventDispatcher;
    }

    /**
     * @param GetPayPalOrderForCheckoutCompletedQuery $getPayPalOrderQuery
     *
     * @return GetPayPalOrderForCheckoutCompletedQueryResult
     *
     * @throws PayPalOrderException
     * @throws OrderException
     * @throws InvalidArgumentException
     */
    public function handle(GetPayPalOrderForCheckoutCompletedQuery $getPayPalOrderQuery)
    {
        /** @var array{id: string, status: string} $order */
        $order = $this->orderPayPalCache->get(CacheSettings::PAYPAL_ORDER_ID . $getPayPalOrderQuery->getOrderId()->getValue());

        if (!empty($order) && $order['status'] === 'APPROVED') {
            return new GetPayPalOrderForCheckoutCompletedQueryResult($order);
        }

        try {
            $orderPayPal = new PaypalOrder($getPayPalOrderQuery->getOrderId()->getValue());
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PayPal Order #%d', $getPayPalOrderQuery->getOrderId()->getValue()), PayPalOrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if (!$orderPayPal->isLoaded()) {
            throw new PayPalOrderException(sprintf('No data for PayPal Order #%d', $getPayPalOrderQuery->getOrderId()->getValue()), PayPalOrderException::EMPTY_ORDER_DATA);
        }

        $this->eventDispatcher->dispatch(
            new PayPalOrderFetchedEvent($getPayPalOrderQuery->getOrderId()->getValue(), $orderPayPal->getOrder())
        );

        $result = new GetPayPalOrderForCheckoutCompletedQueryResult($orderPayPal->getOrder());

        $this->paypalOrderEventDispatcher->dispatch($result->getPayPalOrder());

        return $result;
    }
}
