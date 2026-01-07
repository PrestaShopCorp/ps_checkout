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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The status of the item shipment.
 */
class OrderTrackerStatus
{
    /**
     * The shipment was cancelled and the tracking number no longer applies.
     */
    public const CANCELLED = 'CANCELLED';

    /**
     * The merchant has assigned a tracking number to the items being shipped from the Order. This does not
     * correspond to the carrier's actual status for the shipment. The latest status of the parcel must be
     * retrieved from the carrier.
     */
    public const SHIPPED = 'SHIPPED';

    public const STATUSES = [self::CANCELLED, self::SHIPPED];
}
