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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\CacheInterface;

class PsCheckoutCartRepository
{
    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param int $cartId
     *
     * @return \PsCheckoutCart|false
     *
     * @throws \PrestaShopException
     */
    public function findOneByCartId($cartId)
    {
        if ($this->cache->has(CacheInterface::CART_ID . $cartId)) {
            return $this->cache->get(CacheInterface::CART_ID . $cartId);
        }

        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', (int) $cartId);
        $psCheckoutCartCollection->orderBy('date_upd', 'desc');

        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false !== $psCheckoutCart) {
            $this->cache->setMultiple([
                CacheInterface::CART_ID . $cartId => $psCheckoutCart,
                CacheInterface::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order => $psCheckoutCart,
            ]);
        }

        return $psCheckoutCart;
    }

    /**
     * @param string $payPalOrderId
     *
     * @return \PsCheckoutCart|false
     *
     * @throws \PrestaShopException
     */
    public function findOneByPayPalOrderId($payPalOrderId)
    {
        if ($this->cache->has(CacheInterface::PAYPAL_ORDER_ID . $payPalOrderId)) {
            return $this->cache->get(CacheInterface::PAYPAL_ORDER_ID . $payPalOrderId);
        }

        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('paypal_order', '=', $payPalOrderId);

        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false !== $psCheckoutCart) {
            $this->cache->setMultiple([
                CacheInterface::CART_ID . $psCheckoutCart->id_cart => $psCheckoutCart,
                CacheInterface::PAYPAL_ORDER_ID . $payPalOrderId => $psCheckoutCart,
            ]);
        }

        return $psCheckoutCart;
    }

    /**
     * @param \PsCheckoutCart $psCheckoutCart
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function save(\PsCheckoutCart $psCheckoutCart)
    {
        if (empty($psCheckoutCart->id_cart)) {
            throw new PsCheckoutException('No cart found.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        }

        $success = $psCheckoutCart->save();

        if ($success) {
            $this->cache->setMultiple([
                CacheInterface::CART_ID . $psCheckoutCart->id_cart => $psCheckoutCart,
                CacheInterface::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order => $psCheckoutCart,
            ]);
        }

        return $success;
    }

    /**
     * @param \PsCheckoutCart $psCheckoutCart
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function remove(\PsCheckoutCart $psCheckoutCart)
    {
        if (empty($psCheckoutCart->id_cart)) {
            throw new PsCheckoutException('No cart found.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        }

        $success = $psCheckoutCart->delete();

        if ($success) {
            $this->cache->deleteMultiple([
                CacheInterface::CART_ID . $psCheckoutCart->id_cart,
                CacheInterface::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order,
            ]);
        }

        return $success;
    }
}
