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
    const CANCELED = 'PS_OS_CANCELED';
    const PAYMENT_ERROR = 'PS_OS_ERROR';
    const OUT_OF_STOCK_UNPAID = 'PS_OS_OUTOFSTOCK_UNPAID';
    const OUT_OF_STOCK_PAID = 'PS_OS_OUTOFSTOCK_PAID';
    const PAYMENT_ACCEPTED = 'PS_OS_PAYMENT';
    const REFUNDED = 'PS_OS_REFUND';

    const AUTHORIZED = 'PS_CHECKOUT_STATE_AUTHORIZED';
    //const PARTIALLY_PAID = 'PS_CHECKOUT_STATE_PARTIAL_PAYMENT'; @todo Create a Partial payment state
    const PARTIALLY_PAID = 'PS_OS_PAYMENT';
    const PARTIALLY_REFUNDED = 'PS_CHECKOUT_STATE_PARTIAL_REFUND';
    const WAITING_CAPTURE = 'PS_CHECKOUT_STATE_WAITING_CAPTURE';
    const WAITING_CREDIT_CARD_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT';
    const WAITING_LOCAL_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT';
    const WAITING_PAYPAL_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT';
    const WAITING_PAYMENT = 'PS_CHECKOUT_STATE_WAITING_PAYMENT';

}
