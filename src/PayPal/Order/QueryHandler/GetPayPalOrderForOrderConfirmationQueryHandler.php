<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
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
    private $cache;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param CacheInterface $cache
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, CacheInterface $cache)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
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
        $order = $this->cache->get('paypal_order_id_' . $query->getOrderId()->getValue());

        if (!empty($order) && ($order['status'] === 'PENDING' || $order['status'] === 'COMPLETED')) {
            return new GetPayPalOrderForOrderConfirmationQueryResult($order);
        }

        try {
            $orderPayPal = new PaypalOrder($query->getOrderId()->getValue());
        } catch (\Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PayPal Order #%d', $query->getOrderId()->getValue()), PayPalOrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if (!$orderPayPal->isLoaded()) {
            throw new PayPalOrderException(sprintf('No data for PayPal Order #%d', $query->getOrderId()->getValue()), PayPalOrderException::EMPTY_ORDER_DATA);
        }

        $this->cache->set('paypal_order_id_' . $query->getOrderId()->getValue(), $orderPayPal->getOrder());

        $this->eventDispatcher->dispatch(
            new PayPalOrderFetchedEvent($query->getOrderId()->getValue(), $orderPayPal->getOrder())
        );

        return new GetPayPalOrderForOrderConfirmationQueryResult($orderPayPal->getOrder());
    }
}
