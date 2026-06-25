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

interface ShippingCallbackCacheInterface
{
    /**
     * @param string $cacheKey
     *
     * @return bool
     */
    public function has(string $cacheKey): bool;

    /**
     * @param string $cacheKey
     *
     * @return string|null
     */
    public function getValue(string $cacheKey): ?string;

    /**
     * @param string   $cacheKey
     * @param string   $value
     * @param int|null $ttl seconds; null uses the implementation default
     *
     * @return bool
     */
    public function set(string $cacheKey, string $value, ?int $ttl = null): bool;
}
