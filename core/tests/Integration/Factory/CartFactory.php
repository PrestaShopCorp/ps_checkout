<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use Cart;
use Context;

class CartFactory
{
    /**
     * @param array $data
     *
     * @return Cart
     */
    public static function create(array $data = []): Cart
    {
        $cart = new Cart();

        // Set mandatory fields with default values
        $cart->id_shop = $data['id_shop'] ?? 1;
        $cart->id_customer = $data['id_customer'] ?? 1;
        $cart->id_address_delivery = $data['id_address_delivery'] ?? 1;
        $cart->id_address_invoice = $data['id_address_invoice'] ?? 1;
        $cart->id_currency = $data['id_currency'] ?? 1;
        $cart->id_lang = $data['id_lang'] ?? 1;
        $cart->secure_key = $data['secure_key'] ?? md5(uniqid(rand(), true));

        // Save the cart to the database
        $cart->add();

        return $cart;
    }

    /**
     * @param int $idCart
     *
     * @return void
     */
    public static function delete(int $idCart): void
    {
        $cart = new Cart($idCart);
        if ($cart->id) {
            $cart->delete();
        }
    }
}
