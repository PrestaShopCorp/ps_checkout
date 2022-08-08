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
use Exception;
use Language;
use Order;
use PrestaShop\Module\PrestashopCheckout\Order\CheckoutOrderId;
use PrestaShop\Module\PrestashopCheckout\Order\OrderException;
use Shop;

class AbstractOrderCommandHandler
{
    /**
     * @param CheckoutOrderId $orderId
     *
     * @return Order
     *
     * @throws OrderException
     */
    protected function getOrder($orderId)
    {
        try {
            $order = new Order($orderId);
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Error occurred when trying to get order object #%s', $orderId), OrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if ($order->id !== $orderId) {
            throw new OrderException(sprintf('Order with id "%d" was not found.', $orderId), OrderException::ORDER_NOT_FOUND);
        }

        return $order;
    }

    /**
     * @param Cart $cart
     */
    protected function setCartContext(Cart $cart)
    {
        $context = Context::getContext();
        $context->cart = $cart;
        $context->shop = new Shop($cart->id_shop);
        $context->language = new Language($cart->id_lang);
        $context->customer = new Customer($cart->id_customer);
        $context->currency = Currency::getCurrencyInstance($cart->id_currency);
        $taxAddressType = Configuration::get('PS_TAX_ADDRESS_TYPE');
        $taxAddressId = property_exists($cart, $taxAddressType) ? $cart->{$taxAddressType} : $cart->id_address_delivery;
        $taxAddress = new Address($taxAddressId);
        $context->country = new Country($taxAddress->id_country);
    }
}
