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
 * Indicates if this is a `first` or `subsequent` payment using a stored payment source (also referred
 * to as stored credential or card on file).
 */
class StoredPaymentSourceUsageType
{
    /**
     * Indicates the Initial/First payment with a payment_source that is intended to be stored upon
     * successful processing of the payment.
     */
    public const FIRST = 'FIRST';

    /**
     * Indicates a payment using a stored payment_source which has been successfully used previously for a
     * payment.
     */
    public const SUBSEQUENT = 'SUBSEQUENT';

    /**
     * Indicates that PayPal will derive the value of `FIRST` or `SUBSEQUENT` based on data available to
     * PayPal.
     */
    public const DERIVED = 'DERIVED';

    public const TYPES = [self::FIRST, self::SUBSEQUENT, self::DERIVED];
}
