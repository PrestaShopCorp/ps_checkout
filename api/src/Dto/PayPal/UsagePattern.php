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
 * Expected business/pricing model for the billing agreement., Expected business/charge model for the
 * billing agreement.
 */
class UsagePattern
{
    /**
     * On-demand instant payments – non-recurring, pre-paid, variable amount, variable frequency.
     */
    public const IMMEDIATE = 'IMMEDIATE';

    /**
     * Pay after use, non-recurring post-paid, variable amount, irregular frequency.
     */
    public const DEFERRED = 'DEFERRED';

    /**
     * Pay upfront fixed or variable amount on a fixed date before the goods/service is delivered.
     */
    public const RECURRING_PREPAID = 'RECURRING_PREPAID';

    /**
     * Pay on a fixed date based on usage or consumption after the goods/service is delivered.
     */
    public const RECURRING_POSTPAID = 'RECURRING_POSTPAID';

    /**
     * Charge payer when the set amount is reached or monthly billing cycle, whichever comes first, before
     * the goods/service is delivered.
     */
    public const THRESHOLD_PREPAID = 'THRESHOLD_PREPAID';

    /**
     * Charge payer when the set amount is reached or monthly billing cycle, whichever comes first, after
     * the goods/service is delivered.
     */
    public const THRESHOLD_POSTPAID = 'THRESHOLD_POSTPAID';

    /**
     * Subscription plan where the "amount due" and the "billing frequency" are fixed, and there is no
     * defined duration with the payment due before the good/service is delivered.
     */
    public const SUBSCRIPTION_PREPAID = 'SUBSCRIPTION_PREPAID';

    /**
     * Subscription plan where the "amount due" and the "billing frequency" are fixed, and there is no
     * defined duration with the payment due after the goods/services are delivered.
     */
    public const SUBSCRIPTION_POSTPAID = 'SUBSCRIPTION_POSTPAID';

    /**
     * Unscheduled card on file plan where the merchant can bill buyer upfront based on an agreed logic,
     * but "amount due" and "frequency" can vary. Inclusive of automatic reload plans.
     */
    public const UNSCHEDULED_PREPAID = 'UNSCHEDULED_PREPAID';

    /**
     * Unscheduled card on file plan where the merchant can bill buyer based on an agreed logic, but
     * "amount due" and "frequency" can vary. Inclusive of automatic reload plans.
     */
    public const UNSCHEDULED_POSTPAID = 'UNSCHEDULED_POSTPAID';

    /**
     * Merchant-managed installment plan when the "amount" to be paid and the "billing frequency" are fixed,
     * but there is a defined number of payments with the payment due before the good/service is delivered.
     */
    public const INSTALLMENT_PREPAID = 'INSTALLMENT_PREPAID';

    /**
     * Merchant-managed installment plan when the "amount" to be paid and the "billing frequency" are fixed,
     * but there is a defined number of payments with the payment due after the goods/services are
     * delivered.
     */
    public const INSTALLMENT_POSTPAID = 'INSTALLMENT_POSTPAID';

    public const PATTERNS = [self::IMMEDIATE, self::DEFERRED, self::RECURRING_PREPAID, self::RECURRING_POSTPAID, self::THRESHOLD_PREPAID, self::THRESHOLD_POSTPAID, self::SUBSCRIPTION_PREPAID, self::SUBSCRIPTION_POSTPAID, self::UNSCHEDULED_PREPAID, self::UNSCHEDULED_POSTPAID, self::INSTALLMENT_PREPAID, self::INSTALLMENT_POSTPAID];
}
