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

namespace PsCheckout\Core\PayPal\Order\Cache;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Response\ValueObject\CreatePayPalOrderResponse;

interface PayPalOrderCacheInterface
{
    /**
     * @param CreatePayPalOrderResponse|PayPalOrderResponse $orderResponse
     *
     * @return void
     */
    public function updateOrderCache($orderResponse);

    /**
     * @param string $key the cache item key
     *
     * @return bool
     */
    public function has($key): bool;

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key);

    /**
     * @param string $key
     * @param $value
     * @param $ttl
     *
     * @return bool
     */
    public function set(string $key, $value, $ttl = null): bool;

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key the unique cache key of the item to delete
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete(string $key): bool;
}
