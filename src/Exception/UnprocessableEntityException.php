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

class UnprocessableEntityException extends PsCheckoutException
{
    const UNKNOWN = 0;
    const AMOUNT_MISMATCH = 1;
    const BILLING_ADDRESS_INVALID = 2;
    const CANNOT_BE_NEGATIVE = 3;
    const CANNOT_BE_ZERO_OR_NEGATIVE = 4;
    const CARD_EXPIRED = 5;
    const CITY_REQUIRED = 6;
    const DECIMAL_PRECISION = 7;
    const DONATION_ITEMS_NOT_SUPPORTED = 8;
    const DUPLICATE_REFERENCE_ID = 9;
    const INVALID_CURRENCY_CODE = 10;
    const INVALID_PAYER_ID = 11;
    const ITEM_TOTAL_MISMATCH = 12;
    const ITEM_TOTAL_REQUIRED = 13;
    const MAX_VALUE_EXCEEDED = 14;
    const MISSING_PICKUP_ADDRESS = 15;
    const MULTI_CURRENCY_ORDER = 16;
    const MULTIPLE_ITEM_CATEGORIES = 17;
    const MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED = 18;
    const MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED = 19;
    const PAYEE_ACCOUNT_INVALID = 20;
    const PAYEE_ACCOUNT_LOCKED_OR_CLOSED = 21;
    const PAYEE_ACCOUNT_RESTRICTED = 22;
    const REFERENCE_ID_REQUIRED = 23;
    const PAYMENT_SOURCE_CANNOT_BE_USED = 24;
    const PAYMENT_SOURCE_DECLINED_BY_PROCESSOR = 25;
    const PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED = 26;
    const POSTAL_CODE_REQUIRED = 27;
    const SHIPPING_ADDRESS_INVALID = 28;
    const TAX_TOTAL_MISMATCH = 29;
    const TAX_TOTAL_REQUIRED = 30;
    const UNSUPPORTED_INTENT = 31;
    const UNSUPPORTED_PAYMENT_INSTRUCTION = 32;
    const SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT = 33;
    const UNSUPPORTED_SHIPPING_TYPE = 34;
    const SHIPPING_OPTION_NOT_SELECTED = 35;
    const SHIPPING_OPTIONS_NOT_SUPPORTED = 36;
    const MULTIPLE_SHIPPING_OPTION_SELECTED = 37;
    const PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH = 38;
    const CARD_CLOSED = 39;
    const ORDER_CANNOT_BE_SAVED = 40;
    const SAVE_ORDER_NOT_SUPPORTED = 41;
    const PUI_DUPLICATE_ORDER = 42;
    const INVALID_JSON_POINTER_FORMAT = 43;
    const INVALID_PARAMETER = 44;
    const NOT_PATCHABLE = 45;
    const UNSUPPORTED_PATCH_PARAMETER_VALUE = 46;
    const PATCH_VALUE_REQUIRED = 47;
    const PATCH_PATH_REQUIRED = 48;
    const REFERENCE_ID_NOT_FOUND = 49;
    const ORDER_ALREADY_COMPLETED = 50;
    const AGREEMENT_ALREADY_CANCELLED = 51;
    const BILLING_AGREEMENT_NOT_FOUND = 52;
    const COMPLIANCE_VIOLATION = 53;
    const DOMESTIC_TRANSACTION_REQUIRED = 54;
    const DUPLICATE_INVOICE_ID = 55;
    const INSTRUMENT_DECLINED = 56;
    const ORDER_NOT_APPROVED = 57;
    const MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED = 58;
    const PAYEE_BLOCKED_TRANSACTION = 59;
    const PAYER_ACCOUNT_LOCKED_OR_CLOSED = 60;
    const PAYER_ACCOUNT_RESTRICTED = 61;
    const PAYER_CANNOT_PAY = 62;
    const TRANSACTION_LIMIT_EXCEEDED = 63;
    const TRANSACTION_RECEIVING_LIMIT_EXCEEDED = 64;
    const TRANSACTION_REFUSED = 65;
    const REDIRECT_PAYER_FOR_ALTERNATE_FUNDING = 66;
    const ORDER_ALREADY_CAPTURED = 67;
    const TRANSACTION_BLOCKED_BY_PAYEE = 68;
    const AUTH_CAPTURE_NOT_ENABLED = 69;
    const NOT_ENABLED_FOR_CARD_PROCESSING = 70;
    const PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING = 71;
    const INVALID_PICKUP_ADDRESS = 72;
    const CANNOT_PROCESS_REFUNDS = 73;
    const INVALID_REFUND_AMOUNT = 74;
}
