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

namespace PsCheckout\Api\Dto\PayPal\Payment;

/**
 * The status for the authorized payment.
 */
class AuthorizationStatus
{
    /**
     * The authorized payment is created. No captured payments have been made for this authorized payment.
     */
    public const CREATED = 'CREATED';

    /**
     * The authorized payment has one or more captures against it. The sum of these captured payments is
     * greater than the amount of the original authorized payment.
     */
    public const CAPTURED = 'CAPTURED';

    /**
     * PayPal cannot authorize funds for this authorized payment.
     */
    public const DENIED = 'DENIED';

    /**
     * A captured payment was made for the authorized payment for an amount that is less than the amount of
     * the original authorized payment.
     */
    public const PARTIALLY_CAPTURED = 'PARTIALLY_CAPTURED';

    /**
     * The authorized payment was voided. No more captured payments can be made against this authorized
     * payment.
     */
    public const VOIDED = 'VOIDED';

    /**
     * The created authorization is in pending state. For more information, see status.details.
     */
    public const PENDING = 'PENDING';
}
