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

namespace PsCheckout\Infrastructure\Adapter;

use AddressChecksum;
use CartChecksum;
use Context as PrestashopContext;

class Context implements ContextInterface
{
    /**
     * @var PrestashopContext
     */
    private $context;

    public function __construct()
    {
        $this->context = PrestashopContext::getContext();
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomer()
    {
        return $this->context->customer;
    }

    /**
     * {@inheritdoc}
     */
    public function getCart()
    {
        return $this->context->cart;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->context->country;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->context->language;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency()
    {
        return $this->context->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function getLink()
    {
        return $this->context->link;
    }

    /**
     * {@inheritdoc}
     */
    public function getController()
    {
        return $this->context->controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentThemeName(): string
    {
        return $this->context->shop->theme_name;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyIsoCode(): string
    {
        return $this->context->currency !== null ? $this->context->currency->iso_code : 'EUR';
    }

    /**
     * {@inheritDoc}
     */
    public function getShop()
    {
        return $this->context->shop;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrentCart(\Cart $cart)
    {
        $this->context->cart = $cart;
        $this->context->cart->update();

        if ($cart->id_address_invoice) {
            $this->context->country = (new \Country((new \Address($cart->id_address_invoice))->id_country));
        }

        if ($cart->id_currency) {
            $this->context->currency = (new \Currency($cart->id_currency));
        }

        $this->context->cookie->__set('id_cart', (int) $cart->id);
        $this->context->cookie->write();
    }

    /**
     * {@inheritDoc}
     */
    public function updateCustomer(\Customer $customer)
    {
        $this->context->updateCustomer($customer);
    }

    /**
     * {@inheritDoc}
     */
    public function resetContextCartAddresses()
    {
        $this->context->cart->id_address_delivery = 0;
        $this->context->cart->id_address_invoice = 0;
        $this->context->cart->save();
    }

    /**
     * {@inheritDoc}
     */
    public function setPayPalEmail(string $email): void
    {
        if (\Validate::isEmail($email)) {
            $this->context->cookie->__set('paypalEmail', $email);
            $this->context->cookie->write();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setContextCartAddresses(int $addressId)
    {
        $this->context->cart->id_address_delivery = $addressId;
        $this->context->cart->id_address_invoice = $addressId;

        $products = $this->context->cart->getProducts();

        foreach ($products as $product) {
            $this->context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $addressId);
        }

        return $this->context->cart->save();
    }

    /**
     * {@inheritDoc}
     */
    public function updateCartChecksum()
    {
        $cart = $this->context->cart;
        $cartChecksum = new CartChecksum(new AddressChecksum());

        $selectQuery = new \DbQuery();
        $selectQuery
            ->select('checkout_session_data')
            ->from('cart')
            ->where('id_cart = ' . (int) $cart->id);
        $rawData = \Db::getInstance()->getValue($selectQuery);

        $data = json_decode($rawData ?? '', true);

        if (!is_array($data)) {
            $data = [];
        }

        $data['checksum'] = $cartChecksum->generateChecksum($cart);

        \Db::getInstance()->update(
            'cart',
            [
                'checkout_session_data' => pSQL(json_encode($data)),
            ],
            'id_cart = ' . (int) $cart->id
        );
    }
}
