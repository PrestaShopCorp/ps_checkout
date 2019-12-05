<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Cart;

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
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

        $cartPresenter = new PsCartPresenter();
        $cartPresenter = $cartPresenter->present($this->getCart());

        $shippingAddress = \Address::initialize($cartPresenter['id_address_delivery']);
        $invoiceAddress = \Address::initialize($cartPresenter['id_address_invoice']);

        return [
            'cart' => array_merge(
                $cartPresenter,
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
