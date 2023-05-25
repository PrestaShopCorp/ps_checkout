<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Comparator;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class PayPalCaptureComparator
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $capturePayPal;

    /**
     * @var array
     */
    private $capturePayPalCache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function compare($capturePayPal)
    {
        $this->capturePayPal = $capturePayPal;
        $this->capturePayPalCache = $this->cache->get($this->capturePayPal['id']);

        if (empty($this->capturePayPalCache)) {
            // In case we don't have it in cache

            return false;
        }

        return $this->checkCaptureId() && $this->checkCaptureStatus() && $this->checkUpdateTime() && $this->checkAmount();
    }

    /**
     * @return bool
     */
    private function checkAmount()
    {
        return $this->capturePayPal['amount']['total'] === $this->capturePayPalCache['amount']['total'];
    }

    /**
     * @return bool
     */
    private function checkCaptureId()
    {
        return $this->capturePayPal['id'] === $this->capturePayPalCache['id'];
    }

    /**
     * @return bool
     */
    private function checkCaptureStatus()
    {
        return $this->capturePayPal['status'] === $this->capturePayPalCache['status'];
    }

    /**
     * True if capturePayPal & capturePayPalCache are similar, false if there's a change
     *
     * @return bool
     */
    private function checkUpdateTime()
    {
        if ($this->capturePayPal['status'] !== 'COMPLETED') {
            // We only have update_time in some COMPLETED requests, which mean we can't compare the update_time

            return true;
        }

        if (!isset($this->capturePayPal['update_time']) && isset($this->capturePayPalCache['update_time'])) {
            // We can have an outdated webhook coming through, we keep the recent one

            return true;
        }

        if (isset($this->capturePayPal['update_time']) && !isset($this->capturePayPalCache['update_time'])) {
            // We don't have an update_time in cache but we have an update_time in the new orderPayPal, which means it's recent

            return false;
        }

        if (date_create_from_format('Y-m-d\TH:i:s\Z', $this->capturePayPal['update_time']) > date_create_from_format('Y-m-d\TH:i:s\Z', $this->capturePayPalCache['update_time'])) {
            // The update_time in the new orderPayPal is more recent than the cache one

            return false;
        }

        return true;
    }
}
