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

namespace PsCheckout\Core\PayPal\OrderStatus\Configuration;

class PayPalOrderStatusConfiguration
{
    const STATUS_CREATED = 'CREATED';

    const STATUS_SAVED = 'SAVED';

    const STATUS_APPROVED = 'APPROVED';

    const STATUS_COMPLETED = 'COMPLETED';

    const STATUS_VOIDED = 'VOIDED';

    const STATUS_PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';

    const STATUS_CANCELED = 'CANCELED';

    const STATUS_APPROVAL_REVERSED = 'REVERSED';

    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';

    const STATUS_PARTIALLY_COMPLETED = 'PARTIALLY_COMPLETED';
}
