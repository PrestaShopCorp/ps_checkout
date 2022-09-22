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

class OrderValidationException extends PsCheckoutException
{
    const PSCHECKOUT_PAYER_GIVEN_NAME_INVALID = 47;
    const PSCHECKOUT_PAYER_SURNAME_INVALID = 48;
    const PSCHECKOUT_PAYER_EMAIL_ADDRESS_INVALID = 49;
    const PSCHECKOUT_PAYER_ADDRESS_STREET_INVALID = 50;
    const PSCHECKOUT_PAYER_ADDRESS_CITY_INVALID = 51;
    const PSCHECKOUT_PAYER_ADDRESS_COUNTRY_CODE_INVALID = 52;
    const PSCHECKOUT_PAYER_ADDRESS_POSTAL_CODE_INVALID = 53;
    const PSCHECKOUT_APPLICATION_CONTEXT_BRAND_NAME_INVALID = 54;
    const PSCHECKOUT_APPLICATION_CONTEXT_SHIPPING_PREFERENCE_INVALID = 55;
    const PSCHECKOUT_ITEM_INVALID = 56;
    const PSCHECKOUT_INVALID_INTENT = 57;
    const PSCHECKOUT_CURRENCY_CODE_INVALID = 58;
    const PSCHECKOUT_AMOUNT_EMPTY = 59;
    const PSCHECKOUT_MERCHANT_ID_INVALID = 60;
    const PSCHECKOUT_SHIPPING_NAME_INVALID = 60;
    const PSCHECKOUT_SHIPPING_ADDRESS_INVALID = 61;
    const PSCHECKOUT_SHIPPING_CITY_INVALID = 62;
    const PSCHECKOUT_SHIPPING_COUNTRY_CODE_INVALID = 63;
    const PSCHECKOUT_SHIPPING_POSTAL_CODE_INVALID = 64;
    const PSCHECKOUT_ITEM_INVALID_AMOUNT_CURRENCY = 65;
    const PSCHECKOUT_ITEM_INVALID_AMOUNT_VALUE = 66;
    const PSCHECKOUT_ITEM_INVALID_TAX_CURRENCY = 67;
    const PSCHECKOUT_ITEM_INVALID_TAX_VALUE = 68;
    const PSCHECKOUT_ITEM_INVALID_QUANTITY = 69;
    const PSCHECKOUT_ITEM_INVALID_CATEGORY = 70;
    const PSCHECKOUT_ITEM_ORDER_NOT_FOUND = 71;
}
