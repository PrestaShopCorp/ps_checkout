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
 * The funds that are held on behalf of the merchant.
 */
class DisbursementMode
{
    /**
     * The funds are released to the merchant immediately.
     */
    public const INSTANT = 'INSTANT';

    /**
     * The funds are held for a finite number of days. The actual duration depends on the region and type
     * of integration. You can release the funds through a referenced payout. Otherwise, the funds
     * disbursed automatically after the specified duration.
     */
    public const DELAYED = 'DELAYED';
}
