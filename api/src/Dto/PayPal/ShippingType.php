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
 * A classification for the method of purchase fulfillment.
 */
class ShippingType
{
    /**
     * The payer intends to receive the items at a specified address.
     */
    public const SHIPPING = 'SHIPPING';

    /**
     * DEPRECATED. To ensure that seller protection is correctly assigned, please use 'PICKUP_IN_STORE' or
     * 'PICKUP_FROM_PERSON' instead. Currently, this field indicates that the payer intends to pick up the
     * items at a specified address (ie. a store address).
     */
    public const PICKUP = 'PICKUP';

    /**
     * The payer intends to pick up the item(s) from the payee's physical store. Also termed as BOPIS, "Buy
     * Online, Pick-up in Store". Seller protection is provided with this option.
     */
    public const PICKUP_IN_STORE = 'PICKUP_IN_STORE';

    /**
     * The payer intends to pick up the item(s) from the payee in person. Also termed as BOPIP, "Buy Online,
     * Pick-up in Person". Seller protection is not available, since the payer is receiving the item from
     * the payee in person, and can validate the item prior to payment.
     */
    public const PICKUP_FROM_PERSON = 'PICKUP_FROM_PERSON';

    public const TYPES = [self::SHIPPING, self::PICKUP, self::PICKUP_IN_STORE, self::PICKUP_FROM_PERSON];
}
