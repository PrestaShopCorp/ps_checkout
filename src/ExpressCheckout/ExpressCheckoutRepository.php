<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\ExpressCheckout;


class ExpressCheckoutRepository
{
    public function getOrderPage()
    {
        return (bool)  \Configuration::get(
            ExpressCheckout::PS_CHECKOUT_EC_ORDER_PAGE,
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

    public function getCheckoutPage()
    {
        return (bool)  \Configuration::get(
            ExpressCheckout::PS_CHECKOUT_EC_CHECKOUT_PAGE,
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

    public function getProductPage()
    {
        return (bool)  \Configuration::get(
            ExpressCheckout::PS_CHECKOUT_EC_PRODUCT_PAGE,
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

}
