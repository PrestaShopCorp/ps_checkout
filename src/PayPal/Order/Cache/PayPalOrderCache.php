<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache;

use PsCheckoutCart;
use Symfony\Component\Cache\Simple\ChainCache;

class PayPalOrderCache extends ChainCache
{
    const CACHE_TTL = [
        PsCheckoutCart::STATUS_CREATED => 600,
        PsCheckoutCart::STATUS_PAYER_ACTION_REQUIRED => 600,
        PsCheckoutCart::STATUS_APPROVED => 600,
        PsCheckoutCart::STATUS_VOIDED => 3600,
        PsCheckoutCart::STATUS_SAVED => 3600,
        PsCheckoutCart::STATUS_CANCELED => 3600,
        PsCheckoutCart::STATUS_COMPLETED => 3600,
    ];
    public function set($key, $value, $ttl = null)
    {
        if (!$ttl && isset($value['status']) && isset(self::CACHE_TTL[$value['status']])) {
            $ttl = self::CACHE_TTL[$value['status']];
        }

        return parent::set($key, $value, $ttl);
    }
}
