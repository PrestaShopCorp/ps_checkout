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

namespace PrestaShop\Module\PrestashopCheckout\Order;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class OrderException extends PsCheckoutException
{
    const ORDER_NOT_FOUND = 100;
    const CANNOT_RETRIEVE_ORDER = 101;
    const ORDER_HAS_ALREADY_THIS_STATUS = 102;
    const CANNOT_RETRIEVE_ORDER_STATUS = 103;
    const ORDER_STATUS_NOT_FOUND = 104;
    const UPDATE_ORDER_STATUS_FAILED = 105;
    const MODULE_INSTANCE_NOT_FOUND = 106;
}
