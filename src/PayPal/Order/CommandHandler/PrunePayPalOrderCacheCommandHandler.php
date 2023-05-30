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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\PrunePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCacheUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;

class PrunePayPalOrderCacheCommandHandler
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
     * @param PrunePayPalOrderCacheCommand $prunePayPalOrderCacheCommand
     *
     * @return void
     *
     * @throws PayPalOrderException
     */
    public function handle(PrunePayPalOrderCacheCommand $prunePayPalOrderCacheCommand)
    {
        try {
            // Cache used provide pruning (deletion) of all expired cache items to reduce cache size
            if (method_exists($this->orderPayPalCache, 'prune')) {
                $this->orderPayPalCache->prune();
            }
        } catch (CacheException $exception) {
            throw new PayPalOrderException('Unable to prune PayPal Order Cache', PayPalOrderException::CACHE_EXCEPTION, $exception);
        }

        $this->eventDispatcher->dispatch(
            new PayPalOrderCacheUpdatedEvent($prunePayPalOrderCacheCommand->getOrderId()->getValue())
        );
    }
}
