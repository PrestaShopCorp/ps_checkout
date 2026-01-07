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
 * DEPRECATED. DEPRECATED. The type of landing page to show on the PayPal site for customer checkout.
 * The fields in `application_context` are now available in the `experience_context` object under the
 * `payment_source` which supports them (eg. `payment_source.paypal.experience_context.landing_page`).
 * Please specify this field in the `experience_context` object instead of the `application_context`
 * object.
 *
 * @deprecated
 */
class OrderApplicationContextLandingPage
{
    /**
     * When the customer clicks PayPal Checkout, the customer is redirected to a page to log in to PayPal
     * and approve the payment.
     */
    public const LOGIN = 'LOGIN';

    /**
     * When the customer clicks PayPal Checkout, the customer is redirected to a page to enter credit or
     * debit card and other relevant billing information required to complete the purchase.
     */
    public const BILLING = 'BILLING';

    /**
     * When the customer clicks PayPal Checkout, the customer is redirected to either a page to log in to
     * PayPal and approve the payment or to a page to enter credit or debit card and other relevant billing
     * information required to complete the purchase, depending on their previous interaction with PayPal.
     */
    public const NO_PREFERENCE = 'NO_PREFERENCE';
}
