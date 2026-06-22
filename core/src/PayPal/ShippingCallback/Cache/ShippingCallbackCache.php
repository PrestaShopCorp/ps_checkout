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

namespace PsCheckout\Core\PayPal\ShippingCallback\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ShippingCallbackCache extends ChainAdapter implements ShippingCallbackCacheInterface
{
    /**
     * Default TTL when cert validity cannot be determined: 24 hours.
     * PayPal certs are valid for months, but capping at 24h ensures timely
     * pickup of any emergency cert rotation.
     */
    const DEFAULT_TTL = 86400;

    /**
     * Maximum TTL regardless of the cert's actual notAfter date.
     * Keeps the same cap as DEFAULT_TTL so rotation is always detected within 24h.
     */
    const MAX_TTL = 86400;

    /**
     * Minimum TTL: 1 hour. Prevents a near-expiry cert from being re-fetched
     * on every request in the final minutes of its validity window.
     */
    const MIN_TTL = 3600;

    public function __construct(
        ArrayAdapter $arrayCache,
        FilesystemAdapter $filesystemCache
    ) {
        parent::__construct([$arrayCache, $filesystemCache]);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $cacheKey): bool
    {
        return parent::hasItem($cacheKey);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $cacheKey): ?string
    {
        $item = parent::getItem($cacheKey);

        return $item->isHit() ? (string) $item->get() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $cacheKey, string $value, ?int $ttl = null): bool
    {
        $cacheItem = $this->getItem($cacheKey)
            ->set($value)
            ->expiresAfter($ttl ?? self::DEFAULT_TTL);

        return $this->save($cacheItem);
    }
}
