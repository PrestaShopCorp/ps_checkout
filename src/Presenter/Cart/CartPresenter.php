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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Cart;

use Context;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Present the cart waiting by the create order paypal builder
 */
class CartPresenter implements PresenterInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Present improved cart
     *
     * @return array
     *
     * @throws \Exception
     */
    public function present()
    {
        $productList = $this->context->cart->getProducts();

        $cart = (array) $this->context->cart;

        if (class_exists('\PrestaShop\PrestaShop\Adapter\Cart\CartPresenter')) {
            $cart = new \PrestaShop\PrestaShop\Adapter\Cart\CartPresenter();
            $cart = $cart->present($this->context->cart);
        }

        if (false === isset($cart['totals']['total_including_tax']['amount'])) {
            // Handle native CartPresenter before 1.7.2
            $cart['totals']['total_including_tax']['amount'] = $this->context->cart->getOrderTotal(true);
        }

        $shippingAddress = \Address::initialize((int) $cart['id_address_delivery']);
        $invoiceAddress = \Address::initialize((int) $cart['id_address_invoice']);
        $currency = \Currency::getCurrencyInstance((int) $this->context->cart->id_currency);

        return [
            'cart' => array_merge(
                $cart,
                ['id' => $this->context->cart->id],
                ['shipping_cost' => $this->context->cart->getTotalShippingCost(null, true)]
            ),
            'customer' => \Validate::isLoadedObject($this->context->customer) ? $this->context->customer : new \Customer((int) $this->context->cart->id_customer),
            'language' => $this->context->language,
            'products' => $productList,
            'addresses' => [
                'shipping' => $shippingAddress,
                'invoice' => $invoiceAddress,
            ],
            'currency' => [
                'iso_code' => $currency->iso_code,
            ],
        ];
    }
}
