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
    public function getCartOrderTotal(): ?float
    {
        $cart = $this->context->cart;

        return ($cart !== null && \Validate::isLoadedObject($cart))
            ? (float) $cart->getOrderTotal(true, \Cart::BOTH)
            : null;
    }

    /**
     * {@inheritDoc}
     */
    public function loadCartForWebhook(\Cart $cart): void
    {
        $idShop = (int) $cart->id_shop;
        $idLang = (int) $cart->id_lang;

        $this->context->cart = $cart;
        $this->context->shop = new \Shop($idShop, $idLang);
        // Align the static shop context so shop-scoped Configuration::get() calls (e.g. PS_TAX_ADDRESS_TYPE,
        // PS_COUNTRY_DEFAULT) resolve from the correct shop in a multistore setup.
        \Shop::setContext(\Shop::CONTEXT_SHOP, $idShop);
        $this->context->language = new \Language($idLang, null, $idShop);
        $this->context->currency = new \Currency((int) $cart->id_currency, $idLang, $idShop);

        if ((int) $cart->id_customer > 0) {
            // \Customer::__construct only accepts $id — no $id_lang / $id_shop override
            $this->context->customer = new \Customer((int) $cart->id_customer);
        }

        // Country resolution: try the cart address, fallback to shop default, leave null rather
        // than fatalling if neither resolves (webhook carts may have inconsistent address data).
        $country = $this->resolveWebhookCountry($cart, $idShop);
        if ($country instanceof \Country && \Validate::isLoadedObject($country)) {
            $this->context->country = $country;
        }
    }

    /**
     * Resolves the tax country for a webhook cart.
     *
     * Priority:
     *   1. Address pointed to by PS_TAX_ADDRESS_TYPE (shop-scoped configuration)
     *   2. Shop default country (PS_COUNTRY_DEFAULT, shop-scoped)
     *   3. null — leave context->country unchanged rather than fatalling
     *
     * All PS object loads are guarded with Validate::isLoadedObject() to tolerate deleted
     * or inconsistent address data that is common in webhook-delivered carts.
     *
     * @param \Cart $cart
     * @param int $idShop
     *
     * @return \Country|null
     */
    private function resolveWebhookCountry(\Cart $cart, int $idShop): ?\Country
    {
        // Step 1 — try the address selected by PS_TAX_ADDRESS_TYPE (shop-scoped read)
        $addressType = (string) \Configuration::get('PS_TAX_ADDRESS_TYPE', null, null, $idShop);
        $addressId = $addressType === 'id_address_invoice'
            ? (int) $cart->id_address_invoice
            : (int) $cart->id_address_delivery;

        if ($addressId > 0) {
            $address = new \Address($addressId);
            if (\Validate::isLoadedObject($address) && (int) $address->id_country > 0) {
                $country = new \Country((int) $address->id_country);
                if (\Validate::isLoadedObject($country)) {
                    return $country;
                }
            }
        }

        // Step 2 — fallback to the shop default country (shop-scoped read)
        $defaultCountryId = (int) \Configuration::get('PS_COUNTRY_DEFAULT', null, null, $idShop);
        if ($defaultCountryId > 0) {
            $country = new \Country($defaultCountryId);
            if (\Validate::isLoadedObject($country)) {
                return $country;
            }
        }

        return null;
    }
}
