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

namespace PrestaShop\Module\PrestashopCheckout\Order\CommandHandler;

use Address;
use Cart;
use Configuration;
use Context;
use Country;
use Currency;
use Customer;
use Language;
use Order;
use PrestaShop\Module\PrestashopCheckout\Context\ContextStateManager;
use PrestaShop\Module\PrestashopCheckout\Order\AbstractOrderHandler;
use PrestaShopDatabaseException;
use PrestaShopException;
use Shop;
use Validate;

class AbstractOrderCommandHandler extends AbstractOrderHandler
{
    /**
     * This is the same Language as the $id_lang except in the following case:
     * If $id_lang is invalid (e.g. due to a removed language) $lang_associated is the default language
     *
     * @var Language|null Language identifier
     */
    private $associatedLanguage;

    /**
     * @param ContextStateManager $contextStateManager
     * @param Cart $cart
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function setCartContext(ContextStateManager $contextStateManager, Cart $cart)
    {
        $contextStateManager
            ->saveCurrentContext()
            ->setCart($cart)
            ->setCustomer(new Customer($cart->id_customer))
            ->setCurrency(new Currency($cart->id_currency))
            ->setLanguage($this->getAssociatedLanguage($cart))
            ->setCountry($this->getCartTaxCountry($cart))
            ->setShop(new Shop($cart->id_shop))
        ;
    }

    /**
     * @param ContextStateManager $contextStateManager
     * @param Order $order
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function setOrderContext(ContextStateManager $contextStateManager, Order $order)
    {
        $cart = new Cart($order->id_cart);
        $this->setCartContext($contextStateManager, $cart);
    }

    /**
     * @param Cart $cart
     *
     * @return Country
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getCartTaxCountry(Cart $cart)
    {
        $taxAddressType = Configuration::get('PS_TAX_ADDRESS_TYPE');
        $taxAddressId = property_exists($cart, $taxAddressType) ? $cart->{$taxAddressType} : $cart->id_address_delivery;
        $taxAddress = new Address($taxAddressId);

        return new Country($taxAddress->id_country);
    }

    /**
     * Returns the language related to the cart or the default one if it doesn't exist
     *
     * @param Cart $cart
     *
     * @return Language
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function getAssociatedLanguage(Cart $cart)
    {
        if (null !== $this->associatedLanguage) {
            return $this->associatedLanguage;
        }

        $this->associatedLanguage = new Language($cart->id_lang);

        if (!Validate::isLoadedObject($this->associatedLanguage)) {
            $this->associatedLanguage = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        }

        return $this->associatedLanguage;
    }

    protected function shouldSetCartContext(Context $context, Cart $cart)
    {
        return !Validate::isLoadedObject($context->cart)
            || (int) $context->cart->id !== (int) $cart->id
            || !Validate::isLoadedObject($context->customer)
            || (int) $context->customer->id !== (int) $cart->id_customer
            || !Validate::isLoadedObject($context->shop)
            || (int) $context->shop->id !== (int) $cart->id_shop
            || !Validate::isLoadedObject($context->currency)
            || (int) $context->currency->id !== (int) $cart->id_currency
            || !Validate::isLoadedObject($context->language)
            || (int) $context->language->id !== (int) $cart->id_lang
            || !Validate::isLoadedObject($context->country)
            || (int) $context->country->id !== (int) $this->getCartTaxCountry($cart)->id;
    }
}
