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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * Merchant preference on how the buyer can navigate back to merchant website post approving the
 * transaction on the Venmo App.
 */
class ReturnFlow
{
    /**
     * After payment approval in the PayPal App, buyer will automatically be redirected to the merchant
     * website.
     */
    public const AUTO = 'AUTO';

    /**
     * After payment approval in the PayPal App, buyer will be asked to manually navigate back to the
     * merchant website where they started the transaction from. The buyer is shown a message like 'Return
     * to Merchant' to return to the source where the transaction actually started.
     */
    public const MANUAL = 'MANUAL';
}
