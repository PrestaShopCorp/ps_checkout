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

namespace PsCheckout\Core\PayPal\ShippingTracking\Cache;

interface ShippingTrackingCacheInterface
{
    /**
     * Check if tracking response exists in cache
     *
     * @param string $cacheKey
     *
     * @return bool
     */
    public function has(string $cacheKey): bool;

    /**
     * Get cached tracking response
     *
     * @param string $cacheKey
     *
     * @return mixed
     */
    public function getValue(string $cacheKey);

    /**
     * Set tracking response in cache
     *
     * @param string $cacheKey
     * @param array $value
     * @param int|null $ttl
     *
     * @return bool
     */
    public function set(string $cacheKey, array $value, $ttl = null): bool;

    /**
     * Check if cached response indicates success and should skip API call
     *
     * @param array $cachedResponse
     * @param array $currentPayload Optional payload to compare with cached payload
     *
     * @return bool
     */
    public function shouldSkipApiCall(array $cachedResponse, array $currentPayload = []): bool;
}
