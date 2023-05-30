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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class PayPalOrderComparator
{
    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @var array
     */
    private $newOrderPayPal;

    /**
     * @var array
     */
    private $currentOrderPayPal;

    /**
     * @param CacheInterface $orderPayPalCache
     */
    public function __construct($orderPayPalCache)
    {
        $this->orderPayPalCache = $orderPayPalCache;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function compare($newOrderPayPal)
    {
        $this->newOrderPayPal = $newOrderPayPal;
        $this->currentOrderPayPal = $this->orderPayPalCache->get($this->newOrderPayPal['id']);

        if (empty($this->currentOrderPayPal)) {
            return false;
        }

        return $this->checkOrderId() && $this->checkOrderStatus() && $this->checkUpdateTime();
    }

    /**
     * @return bool
     */
    private function checkOrderId()
    {
        return $this->newOrderPayPal['id'] === $this->currentOrderPayPal['id'];
    }

    /**
     * @return bool
     */
    private function checkOrderStatus()
    {
        return $this->newOrderPayPal['status'] === $this->currentOrderPayPal['status'];
    }

    /**
     * True if orderPayPal & orderPayPalCache are similar, false if there's a change
     *
     * @return bool
     */
    private function checkUpdateTime()
    {
        if ($this->newOrderPayPal['status'] !== 'COMPLETED') {
            // We only have update_time in some COMPLETED requests, which mean we can't compare the update_time

            return true;
        }

        if (!isset($this->newOrderPayPal['update_time']) && isset($this->currentOrderPayPal['update_time'])) {
            // We can have an outdated webhook coming through, we keep the recent one

            return true;
        }

        if (isset($this->newOrderPayPal['update_time']) && !isset($this->currentOrderPayPal['update_time'])) {
            // We don't have an update_time in cache but we have an update_time in the new orderPayPal, which means it's recent

            return false;
        }

        if (date_create_from_format('Y-m-d\TH:i:s\Z', $this->newOrderPayPal['update_time']) > date_create_from_format('Y-m-d\TH:i:s\Z', $this->currentOrderPayPal['update_time'])) {
            // The update_time in the new orderPayPal is more recent than the cache one

            return false;
        }

        return true;
    }
}
