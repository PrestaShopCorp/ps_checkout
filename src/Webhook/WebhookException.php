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

namespace PrestaShop\Module\PrestashopCheckout\Webhook;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class WebhookException extends PsCheckoutException
{
    const WEBHOOK_SECRET_MISMATCH = 1;
    const WEBHOOK_PAYLOAD_INVALID = 2;
    const WEBHOOK_PAYLOAD_UNSUPPORTED = 3;
    const WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING = 4;
    const WEBHOOK_PAYLOAD_RESOURCE_MISSING = 5;
    const WEBHOOK_PAYLOAD_CONFIGURATION_LIST_MISSING = 6;
    const WEBHOOK_PAYLOAD_CONFIGURATION_NAME_INVALID = 7;
    const WEBHOOK_PAYLOAD_CONFIGURATION_VALUE_INVALID = 8;
}
