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

namespace PrestaShop\Module\PrestashopCheckout\Order\Exception;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class OrderException extends PsCheckoutException
{
    const INVALID_ID = 1;
    const ORDER_NOT_FOUND = 2;
    const INVALID_CURRENCY = 3;
    const FAILED_ADD_PAYMENT = 4;
    const INVALID_INVOICE = 5;
    const FAILED_ADD_ORDER = 6;
    const ORDER_HAS_ALREADY_THIS_STATUS = 7;
    const FAILED_UPDATE_ORDER_STATUS = 8;
    const ORDER_STATUS_NOT_FOUND = 9;
    const MODULE_INSTANCE_NOT_FOUND = 10;
    const ORDER_MATRICE_ERROR = 11;
    const ORDER_CHECK_AMOUNT_BAD_PARAMETER = 12;
    const STATUS_CHECK_AVAILABLE_BAD_PARAMETER = 13;
    const INVALID_PAYPAL_ORDER_STATE = 14;
    const TRANSITION_NOT_ALLOWED = 15;
}
