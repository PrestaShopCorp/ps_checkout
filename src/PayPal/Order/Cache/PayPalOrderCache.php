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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ChainAdapter;

class PayPalOrderCache extends ChainAdapter
{
    public const CACHE_TTL = [
        \PsCheckoutCart::STATUS_CREATED => 600,
        \PsCheckoutCart::STATUS_PAYER_ACTION_REQUIRED => 600,
        \PsCheckoutCart::STATUS_APPROVED => 600,
        \PsCheckoutCart::STATUS_VOIDED => 3600,
        \PsCheckoutCart::STATUS_SAVED => 3600,
        \PsCheckoutCart::STATUS_CANCELED => 3600,
        \PsCheckoutCart::STATUS_COMPLETED => 3600,
    ];

    /**
     * @param string $key
     * @param array $value
     * @param int $ttl
     *
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        if (!$ttl && isset($value['status'], self::CACHE_TTL[$value['status']])) {
            $ttl = self::CACHE_TTL[$value['status']];
        }

        $cacheItem = $this->getItem($key)->set($value)->expiresAfter($ttl);

        return $this->save($cacheItem);
    }
}
