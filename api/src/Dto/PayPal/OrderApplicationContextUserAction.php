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
 * DEPRECATED. Configures a Continue or Pay Now checkout flow.  The fields in `application_context` are
 * now available in the `experience_context` object under the `payment_source` which supports them (eg.
 * `payment_source.paypal.experience_context.user_action`). Please specify this field in the
 * `experience_context` object instead of the `application_context` object.
 *
 * @deprecated
 */
class OrderApplicationContextUserAction
{
    /**
     * After you redirect the customer to the PayPal payment page, a Continue button appears. Use this
     * option when the final amount is not known when the checkout flow is initiated and you want to
     * redirect the customer to the merchant page without processing the payment.
     */
    public const CONTINUE_ = 'CONTINUE';

    /**
     * After you redirect the customer to the PayPal payment page, a Pay Now button appears. Use this
     * option when the final amount is known when the checkout is initiated and you want to process the
     * payment immediately when the customer clicks Pay Now.
     */
    public const PAY_NOW = 'PAY_NOW';
}
