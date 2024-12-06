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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\Exception;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class ApplePaySetupException extends PsCheckoutException
{
    const FAILED_REGISTER_HOOK = 1001;
    const ERROR_REGISTER_HOOK = 1002;
    const UNABLE_RETRIEVE_ROOT_DIR = 2001;
    const FAILED_CREATE_WELL_KNOWN_DIR = 2002;
    const WELL_KNOWN_DIR_NOT_WRITABLE = 2003;
    const PRESTASHOP_NOT_AT_DOMAIN_ROOT = 2004;
    const UNABLE_RETRIEVE_BASE_URI = 2005;
    const APPLE_DOMAIN_FILE_NOT_FOUND = 3001;
    const APPLE_DOMAIN_FILE_NOT_WRITABLE = 3002;
    const FAILED_COPY_APPLE_DOMAIN_FILE = 3003;
}
