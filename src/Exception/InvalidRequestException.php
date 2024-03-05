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

class InvalidRequestException extends PsCheckoutException
{
    const UNKNOWN = 0;
    const INVALID_ARRAY_MAX_ITEMS = 1;
    const INVALID_ARRAY_MIN_ITEMS = 2;
    const INVALID_COUNTRY_CODE = 3;
    const INVALID_PARAMETER_SYNTAX = 4;
    const INVALID_STRING_LENGTH = 5;
    const INVALID_PARAMETER_VALUE = 6;
    const MISSING_REQUIRED_PARAMETER = 7;
    const NOT_SUPPORTED = 8;
    const PAYPAL_REQUEST_ID_REQUIRED = 9;
    const MALFORMED_REQUEST_JSON = 10;
    const FIELD_NOT_PATCHABLE = 11;
    const AMOUNT_NOT_PATCHABLE = 12;
    const INVALID_PATCH_OPERATION = 13;
}
