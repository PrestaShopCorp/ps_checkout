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

use PrestaShop\Module\PrestashopCheckout\Cart\Cache\CacheSettings;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use Psr\SimpleCache\CacheInterface;

class PsCheckoutCartRepository
{
    /**
     * @var CacheInterface
     */
    private $cartPrestaShopCache;

    /**
     * @param CacheInterface $cartPrestaShopCache
     */
    public function __construct(CacheInterface $cartPrestaShopCache)
    {
        $this->cartPrestaShopCache = $cartPrestaShopCache;
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
        if ($this->cartPrestaShopCache->has(CacheSettings::CART_ID . $cartId)) {
            return $this->cartPrestaShopCache->get(CacheSettings::CART_ID . $cartId);
        }

        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', (int) $cartId);
        $psCheckoutCartCollection->orderBy('date_upd', 'desc');

        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false !== $psCheckoutCart) {
            $this->cartPrestaShopCache->setMultiple([
                CacheSettings::CART_ID . $cartId => $psCheckoutCart,
                CacheSettings::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order => $psCheckoutCart,
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
        if ($this->cartPrestaShopCache->has(CacheSettings::PAYPAL_ORDER_ID . $payPalOrderId)) {
            return $this->cartPrestaShopCache->get(CacheSettings::PAYPAL_ORDER_ID . $payPalOrderId);
        }

        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('paypal_order', '=', $payPalOrderId);

        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false !== $psCheckoutCart) {
            $this->cartPrestaShopCache->setMultiple([
                CacheSettings::CART_ID . $psCheckoutCart->id_cart => $psCheckoutCart,
                CacheSettings::PAYPAL_ORDER_ID . $payPalOrderId => $psCheckoutCart,
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
            $this->cartPrestaShopCache->setMultiple([
                CacheSettings::CART_ID . $psCheckoutCart->id_cart => $psCheckoutCart,
                CacheSettings::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order => $psCheckoutCart,
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
            $this->cartPrestaShopCache->deleteMultiple([
                CacheSettings::CART_ID . $psCheckoutCart->id_cart,
                CacheSettings::PAYPAL_ORDER_ID . $psCheckoutCart->paypal_order,
            ]);
        }

        return $success;
    }
}
