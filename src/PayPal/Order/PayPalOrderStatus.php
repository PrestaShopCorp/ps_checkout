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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order;

class PayPalOrderStatus
{
    const CREATED = 'CREATED';
    const SAVED = 'SAVED';
    const APPROVED = 'APPROVED';
    const PENDING_APPROVAL = 'PENDING_APPROVAL';
    const PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';
    const VOIDED = 'VOIDED';
    const COMPLETED = 'COMPLETED';
    const CANCELED = 'CANCELED';
    const REVERSED = 'REVERSED';

    const TRANSITION_AVAILABLE = [
        self::CREATED => [
            self::APPROVED,
            self::PENDING_APPROVAL,
            self::SAVED,
            self::PAYER_ACTION_REQUIRED,
            self::VOIDED,
            self::COMPLETED,
            self::CANCELED,
        ],
        self::SAVED => [
            self::VOIDED,
        ],
        self::APPROVED => [
            self::PAYER_ACTION_REQUIRED,
            self::COMPLETED,
            self::REVERSED,
            self::CANCELED,
        ],
        self::PENDING_APPROVAL => [
            self::PAYER_ACTION_REQUIRED,
            self::APPROVED,
            self::CANCELED,
        ],
        self::PAYER_ACTION_REQUIRED => [
            self::PENDING_APPROVAL,
            self::APPROVED,
            self::COMPLETED,
            self::CANCELED,
        ],
        self::VOIDED => [],
        self::COMPLETED => [],
        self::CANCELED => [],
        self::REVERSED => [],
    ];
}
