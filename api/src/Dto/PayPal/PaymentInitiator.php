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
 * The person or party who initiated or triggered the payment.
 */
class PaymentInitiator
{
    /**
     * Payment is initiated with the active engagement of the customer. e.g. a customer checking out on a
     * merchant website.
     */
    public const CUSTOMER = 'CUSTOMER';

    /**
     * Payment is initiated by merchant on behalf of the customer without the active engagement of customer.
     * e.g. a merchant charging the monthly payment of a subscription to the customer.
     */
    public const MERCHANT = 'MERCHANT';

    public const INITIATORS = [self::CUSTOMER, self::MERCHANT];
}
