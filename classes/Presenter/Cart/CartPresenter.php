<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Cart;

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter as PsCartPresenter;

/**
 * Present the cart waiting by the create order paypal builder
 */
class CartPresenter implements PresenterInterface
{
    /**
     * @var \Cart
     */
    private $cart;

    /**
     * @var \Customer
     */
    private $customer;

    /**
     * @var \Language
     */
    private $language;

    public function __construct(\Context $context)
    {
        $this->setCart($context->cart);
        $this->setCustomer($context->customer);
        $this->setLanguage($context->language);
    }

    /**
     * Present improved cart
     *
     * @return array
     */
    public function present()
    {
        $productList = $this->getCart()->getProducts();

        $cart = (array) $this->getCart();

        if ((new ShopContext())->isShop17()) {
            $cart = new PsCartPresenter();
            $cart = $cart->present($this->getCart());
        }

        if (!isset($cart['totals']['total_including_tax']['amount'])) {
            // Handle native CartPresenter before 1.7.2
            $cart['totals']['total_including_tax']['amount'] = $this->getCart()->getOrderTotal(true);
        }

        $shippingAddress = \Address::initialize($cart['id_address_delivery']);
        $invoiceAddress = \Address::initialize($cart['id_address_invoice']);

        return [
            'cart' => array_merge(
                $cart,
                ['id' => $this->getCart()->id],
                ['shipping_cost' => $this->getCart()->getTotalShippingCost(null, true)]
            ),
            'customer' => $this->getCustomer(),
            'language' => $this->getLanguage(),
            'products' => $productList,
            'addresses' => [
                'shipping' => $shippingAddress,
                'invoice' => $invoiceAddress,
            ],
            'currency' => [
                'iso_code' => $this->getCurrencyIsoFromId($this->getCart()->id_currency),
            ],
        ];
    }

    /**
     * Get currency iso code from id currency
     *
     * @param int $currencyId
     *
     * @return string Currency iso code
     */
    private function getCurrencyIsoFromId($currencyId)
    {
        $currency = \Currency::getCurrency($currencyId);

        return $currency['iso_code'];
    }

    /**
     * setter
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * setter
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * setter
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * getter
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * getter
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * getter
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
