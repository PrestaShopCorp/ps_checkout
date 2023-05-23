<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator;

use Psr\SimpleCache\CacheInterface;

class PayPalOrderComparator
{
    /**
     * @var array
     */
    private $orderPayPal;

    /**
     * @var array
     */
    private $orderPayPalCache;

    /**
     * @param array $orderPayPal
     * @param array $orderPayPalCache
     */
    public function __construct($orderPayPal, $orderPayPalCache)
    {
        $this->orderPayPal = $orderPayPal;
        $this->orderPayPalCache = $orderPayPalCache;
    }

    /**
     * @return bool
     */
    public function isOrderPayPalSimilar()
    {
        switch (false) {
            case $this->checkOrderId():
            case $this->checkOrderStatus():
            case $this->checkUpdateTime():
                return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    private function checkOrderId()
    {
        return $this->orderPayPal['id'] === $this->orderPayPalCache['id'];
    }

    /**
     * @return bool
     */
    private function checkOrderStatus()
    {
        return $this->orderPayPal['status'] === $this->orderPayPalCache['status'];
    }

    /**
     * True if orderPayPal & orderPayPalCache are similar, false if there's a change
     * 
     * @return bool 
     */
    private function checkUpdateTime()
    {
        if ($this->orderPayPal['status'] !== 'COMPLETED') {
            # We only have update_time in some COMPLETED requests, which mean we can't compare the update_time

            return true;
        }

        if (!isset($this->orderPayPal['update_time']) && isset($this->orderPayPalCache['update_time'])) {
            # We can have an outdated webhook coming throught, we keep the recent one

            return true;
        }

        if (isset($this->orderPayPal['update_time']) && !isset($this->orderPayPalCache['update_time'])) {
            # We don't have an update_time in cache but we have an update_time in the new orderPayPal, which means it's recent

            return false;
        }

        if (date_create_from_format('Y-m-d\TH:i:s\Z', $this->orderPayPal['update_time']) > date_create_from_format('Y-m-d\TH:i:s\Z', $this->orderPayPalCache['update_time'])) {
            # The update_time in the new orderPayPal is more recent than the cache one

            return false;
        }

        return true;
    }
}
