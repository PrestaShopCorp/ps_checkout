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

namespace PrestaShop\Module\PrestashopCheckout\Cart\Query;

use Cart;
use Currency;
use Customer;

class GetCartForPaymentQueryHandler
{
    public function handle(GetCartForPaymentQuery $query)
    {
        $cart = new Cart($query->getCartId()->getValue());
        $currency = new Currency($cart->id_currency);
        $language = $cart->getAssociatedLanguage();

        return new GetCartForPaymentQueryResult($cart, $cart->getProducts());
    }

    /**
     * @param Cart $cart
     *
     * @return CartAddress[]
     */
    private function getAddresses(Cart $cart)
    {
        $customer = new Customer($cart->id_customer);
        $cartAddresses = [];

        foreach ($customer->getAddresses($cart->getAssociatedLanguage()->getId()) as $data) {
            $addressId = (int) $data['id_address'];
            $cartAddresses[$addressId] = $this->buildCartAddress($addressId, $cart);
        }

        // Add addresses already assigned to cart if absent (in case they are deleted)
        if (0 !== (int) $cart->id_address_delivery && !isset($cartAddresses[$cart->id_address_delivery])) {
            $cartAddresses[$cart->id_address_delivery] = $this->buildCartAddress(
                $cart->id_address_delivery,
                $cart
            );
        }
        if (0 !== (int) $cart->id_address_invoice && !isset($cartAddresses[$cart->id_address_invoice])) {
            $cartAddresses[$cart->id_address_invoice] = $this->buildCartAddress(
                $cart->id_address_invoice,
                $cart
            );
        }

        return array_values($cartAddresses);
    }
}
