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

namespace PsCheckout\Core\Tests\Integration\Factory;

use Cart;

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
