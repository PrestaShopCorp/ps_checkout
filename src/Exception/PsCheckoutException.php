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

namespace PrestaShop\Module\PrestashopCheckout\Exception;

class PsCheckoutException extends \Exception
{
    const UNKNOWN = 0;
    const PRESTASHOP_ORDER_NOT_FOUND = 2;
    const PRESTASHOP_REFUND_ALREADY_SAVED = 3;
    const PRESTASHOP_REFUND_TOTAL_AMOUNT_REACHED = 4;
    const PRESTASHOP_ORDER_STATE_ERROR = 5;
    const PRESTASHOP_CONTEXT_INVALID = 6;
    const PRESTASHOP_PAYMENT_UNAVAILABLE = 7;
    const PSACCOUNT_TOKEN_MISSING = 8;
    const PSACCOUNT_REFRESH_TOKEN_MISSING = 9;
    const PSCHECKOUT_LOCALE_DECODE_ERROR = 10;
    const PSCHECKOUT_MERCHANT_IDENTIFIER_MISSING = 11;
    const PSCHECKOUT_ORDER_MATRICE_ERROR = 12;
    const PSCHECKOUT_WEBHOOK_HEADER_EMPTY = 13;
    const PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY = 14;
    const PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY = 15;
    const PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY = 16;
    const PSCHECKOUT_WEBHOOK_BODY_EMPTY = 17;
    const PSCHECKOUT_WEBHOOK_EVENT_TYPE_EMPTY = 18;
    const PSCHECKOUT_WEBHOOK_CATEGORY_EMPTY = 19;
    const PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY = 20;
    const PSCHECKOUT_WEBHOOK_AMOUNT_EMPTY = 21;
    const PSCHECKOUT_WEBHOOK_AMOUNT_INVALID = 22;
    const PSCHECKOUT_WEBHOOK_CURRENCY_EMPTY = 23;
    const PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY = 24;
    const PSCHECKOUT_WEBHOOK_PSL_SIGNATURE_INVALID = 25;
    const PSCHECKOUT_WEBHOOK_SHOP_ID_INVALID = 26;
    const PAYPAL_ORDER_IDENTIFIER_MISSING = 27;
    const PAYPAL_PAYMENT_METHOD_MISSING = 28;
    const PAYPAL_PAYMENT_CARD_ERROR = 29;
    const PAYPAL_PAYMENT_CAPTURE_DECLINED = 30;
    const PRESTASHOP_ORDER_ID_MISSING = 31;
    const PSCHECKOUT_BAD_STATIC_TOKEN = 32;
    const PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_CUSTOMER = 33;
    const PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS = 34;
    const PSCHECKOUT_HTTP_EXCEPTION = 35;
    const PSCHECKOUT_LOGGER_FILE_READER_NOT_FOUND = 36;
    const PSCHECKOUT_LOGGER_FILE_READER_NOT_READABLE = 37;
    const PSCHECKOUT_VALIDATE_BODY_EMPTY = 38;
    const PSCHECKOUT_UPDATE_ORDER_HANDLE_ERROR = 39;
    const PRESTASHOP_VALIDATE_ORDER = 40;
    const PRESTASHOP_ORDER_PAYMENT = 41;
    const PRESTASHOP_CART_NOT_FOUND = 42;
    const DIFFERENCE_BETWEEN_TRANSACTION_AND_CART = 43;
    const PSL_TIMEOUT = 44;
    const MISSING_PAYPAL_CLIENT_TOKEN = 46;

    const EXCEPTIONS_IGNORED_BY_SENTRY = [
        self::PRESTASHOP_CART_NOT_FOUND,
        self::PRESTASHOP_CONTEXT_INVALID,
        self::PRESTASHOP_PAYMENT_UNAVAILABLE,
        self::PSCHECKOUT_VALIDATE_BODY_EMPTY,
        self::PAYPAL_ORDER_IDENTIFIER_MISSING,
    ];
}
