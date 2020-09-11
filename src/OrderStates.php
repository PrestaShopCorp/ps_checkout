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

namespace PrestaShop\Module\PrestashopCheckout;

class OrderStates
{
    const DARK_BLUE_HEXA_COLOR = '#34209E';
    const BLUE_HEXA_COLOR = '#3498D8';
    const GREEN_HEXA_COLOR = '#01B887';
    const PS_CHECKOUT_STATE_AUTHORIZED = 'PS_CHECKOUT_STATE_AUTHORIZED';
    const PS_CHECKOUT_STATE_PARTIAL_REFUND = 'PS_CHECKOUT_STATE_PARTIAL_REFUND';
    const ORDER_STATES = [
        'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        self::PS_CHECKOUT_STATE_PARTIAL_REFUND => self::GREEN_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_CAPTURE' => self::BLUE_HEXA_COLOR,
    ];
}
