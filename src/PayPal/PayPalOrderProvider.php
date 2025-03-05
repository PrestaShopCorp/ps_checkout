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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\PayPalOrderCache;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Contracts\Cache\ItemInterface;

class PayPalOrderProvider
{
    public function __construct(private ChainAdapter $orderPayPalCache)
    {
    }

    /**
     * @param string $id PayPal Order Id
     *
     * @return array|false
     */
    public function getById($id)
    {
        if (empty($id)) {
            return false;
        }

        return $this->orderPayPalCache->get($id, function (ItemInterface $cacheItem) use ($id) {
            $orderPayPal = new PaypalOrder($id);

            if (!$orderPayPal->isLoaded()) {
                return false;
            }
            $order = $orderPayPal->getOrder();

            $cacheItem->expiresAfter(PayPalOrderCache::CACHE_TTL[$order['status']]);

            return $order;
        });
    }
}
