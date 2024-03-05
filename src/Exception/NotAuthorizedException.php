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

class NotAuthorizedException extends PsCheckoutException
{
    const UNKNOWN = 0;
    const PERMISSION_DENIED = 1;
    const PERMISSION_DENIED_FOR_DONATION_ITEMS = 2;
    const MALFORMED_REQUEST = 3;
    const PAYEE_ACCOUNT_NOT_SUPPORTED = 4;
    const PAYEE_ACCOUNT_NOT_VERIFIED = 5;
    const PAYEE_NOT_CONSENTED = 6;
    const INVALID_TOKEN = 7;
    const CONSENT_NEEDED = 8;
}
