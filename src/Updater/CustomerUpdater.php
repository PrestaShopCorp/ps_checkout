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

namespace PrestaShop\Module\PrestashopCheckout\Updater;

/**
 * This is used only on PrestaShop 1.6
 */
class CustomerUpdater
{
    public static function updateContextCustomer(\Context $context, \Customer $customer)
    {
        $context->customer = $customer;
        $context->cookie->__set('id_customer', (int) $customer->id);
        $context->cookie->__set('customer_lastname', $customer->lastname);
        $context->cookie->__set('customer_firstname', $customer->firstname);
        $context->cookie->__set('passwd', $customer->passwd);
        $context->cookie->__set('logged', 1);
        $context->cookie->__set('id_customer', (int) $customer->id);
        $customer->logged = true;
        $context->cookie->__set('email', $customer->email);
        $context->cookie->__set('is_guest', $customer->isGuest());

        if (\Configuration::get('PS_CART_FOLLOWING') && (empty($context->cookie->id_cart) || \Cart::getNbProducts((int) $context->cookie->__get('id_cart')) == 0) && $idCart = (int) \Cart::lastNoneOrderedCart((int) $context->customer->id)) {
            $context->cart = new \Cart($idCart);
            $context->cart->secure_key = $customer->secure_key;
        } else {
            $idCarrier = (int) $context->cart->id_carrier;
            $context->cart->secure_key = $customer->secure_key;
            $context->cart->id_carrier = 0;
            $context->cart->setDeliveryOption(null);
            $context->cart->updateAddressId($context->cart->id_address_delivery, (int) \Address::getFirstCustomerAddressId((int) $customer->id));
            $context->cart->id_address_delivery = (int) \Address::getFirstCustomerAddressId((int) $customer->id);
            $context->cart->id_address_invoice = (int) \Address::getFirstCustomerAddressId((int) $customer->id);
        }
        $context->cart->id_customer = (int) $customer->id;

        if (isset($idCarrier) && $idCarrier) {
            $deliveryOption = [$context->cart->id_address_delivery => $idCarrier . ','];
            $context->cart->setDeliveryOption($deliveryOption);
        }

        $context->cart->save();
        $context->cookie->__set('id_cart', (int) $context->cart->id);
        $context->cookie->write();
        $context->cart->autosetProductAddress();
    }
}
