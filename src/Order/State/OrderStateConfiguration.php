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

namespace PrestaShop\Module\PrestashopCheckout\Order\State;

class OrderStateConfiguration
{
    const PS_OS_CANCELED = 0;
    const PS_OS_ERROR = 1;
    const PS_OS_OUT_OF_STOCK_UNPAID = 2;
    const PS_OS_OUT_OF_STOCK_PAID = 3;
    const PS_OS_PAYMENT = 4;
    const PS_OS_REFUND = 5;

    const PS_CHECKOUT_STATE_AUTHORIZED = 6;
    const PS_CHECKOUT_STATE_PARTIAL_PAYMENT = 7;
    const PS_CHECKOUT_STATE_PARTIAL_REFUND = 8;
    const PS_CHECKOUT_STATE_WAITING_CAPTURE = 9;
    const PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT = 10;
    const PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT = 11;
    const PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT = 12;
}
