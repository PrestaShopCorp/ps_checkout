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

class OrderStateConfigurationKeys
{
    // PrestaShop native order statuses
    const PS_OS_CANCELED = 'PS_OS_CANCELED';
    const PS_OS_ERROR = 'PS_OS_ERROR';
    const PS_OS_OUTOFSTOCK_UNPAID = 'PS_OS_OUTOFSTOCK_UNPAID';
    const PS_OS_OUTOFSTOCK_PAID = 'PS_OS_OUTOFSTOCK_PAID';
    const PS_OS_PAYMENT = 'PS_OS_PAYMENT';
    const PS_OS_REFUND = 'PS_OS_REFUND';

    // PrestaShop Checkout order statuses
    const PS_CHECKOUT_STATE_PENDING = 'PS_CHECKOUT_STATE_PENDING';
    const PS_CHECKOUT_STATE_COMPLETED = 'PS_CHECKOUT_STATE_COMPLETED';
    const PS_CHECKOUT_STATE_CANCELED = 'PS_CHECKOUT_STATE_CANCELED';
    const PS_CHECKOUT_STATE_ERROR = 'PS_CHECKOUT_STATE_ERROR';
    const PS_CHECKOUT_STATE_REFUNDED = 'PS_CHECKOUT_STATE_REFUNDED';
    const PS_CHECKOUT_STATE_PARTIALLY_REFUNDED = 'PS_CHECKOUT_STATE_PARTIALLY_REFUNDED';
    const PS_CHECKOUT_STATE_PARTIALLY_PAID = 'PS_CHECKOUT_STATE_PARTIALLY_PAID';
    const PS_CHECKOUT_STATE_AUTHORIZED = 'PS_CHECKOUT_STATE_AUTHORIZED';

    // PrestaShop Checkout deprecated order statuses
    const PS_CHECKOUT_STATE_PARTIAL_REFUND = 'PS_CHECKOUT_STATE_PARTIAL_REFUND';
    const PS_CHECKOUT_STATE_WAITING_CAPTURE = 'PS_CHECKOUT_STATE_WAITING_CAPTURE';
    const PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT';
    const PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT';
    const PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT';
}
