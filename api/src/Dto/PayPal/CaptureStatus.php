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
 * The status of the captured payment.
 */
class CaptureStatus
{
    /**
     * The funds for this captured payment were credited to the payee's PayPal account.
     */
    public const COMPLETED = 'COMPLETED';

    /**
     * The funds could not be captured.
     */
    public const DECLINED = 'DECLINED';

    /**
     * An amount less than this captured payment's amount was partially refunded to the payer.
     */
    public const PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';

    /**
     * The funds for this captured payment was not yet credited to the payee's PayPal account. For more
     * information, see status.details.
     */
    public const PENDING = 'PENDING';

    /**
     * An amount greater than or equal to this captured payment's amount was refunded to the payer.
     */
    public const REFUNDED = 'REFUNDED';

    /**
     * There was an error while capturing payment.
     */
    public const FAILED = 'FAILED';

    public const STATUSES = [self::COMPLETED, self::DECLINED, self::PARTIALLY_REFUNDED, self::PENDING, self::REFUNDED, self::FAILED];
}
