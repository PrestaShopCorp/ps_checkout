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

namespace PrestaShop\Module\PrestashopCheckout\OrderState\Exception;

class OrderStateException extends \Exception
{
    const UNKNOWN = 0;
    const ORDER_STATE_NOT_CREATED = 1;
    const ORDER_STATE_CONFIGURATION_NOT_SAVED = 2;
    const ORDER_STATE_EMPTY_NAME = 3;
    const ORDER_STATE_INVALID_CONFIGURATION_KEY = 4;
    const ORDER_STATE_INVALID_ID = 5;
    const ORDER_STATE_NOT_UPDATED = 6;
    const ORDER_STATE_CONFIGURATION_NOT_DELETED = 7;
    const ORDER_STATE_INVALID_ICON_PATH = 8;
    const ORDER_STATE_ICON_NOT_COPIED = 9;
}
