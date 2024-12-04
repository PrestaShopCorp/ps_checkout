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

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$ttl && isset($value['status'])) {
            $status = $value['status'];
            if (self::CACHE_TTL[$status] !== null) {
                $ttl = self::CACHE_TTL[$status];
            }
        }

        return parent::set($key, $value, $ttl);
    }
}
