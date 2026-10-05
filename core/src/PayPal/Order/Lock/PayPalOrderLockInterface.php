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

namespace PsCheckout\Core\PayPal\Order\Lock;

/**
 * Serializes the processing of one PayPal order across concurrent requests.
 *
 * The same "payment completed" event is handled twice: synchronously in the
 * front-office request that captured the order, and again when PayPal delivers
 * the webhook. When the second request starts while the first one is still inside
 * PaymentModule::validateOrder(), the PrestaShop order row already exists but its
 * order details, payment and invoice do not yet: the duplicate-detection checks
 * (order exists, order payment exists, order state already set) all pass, and the
 * second request creates the invoice before the order details are inserted, adds a
 * second payment line, and sets the order state a second time.
 */
interface PayPalOrderLockInterface
{
    /**
     * Blocks until the lock for this PayPal order is available, or gives up after an
     * implementation-defined delay.
     *
     * @param string $payPalOrderId
     *
     * @return bool true when the lock is held by the caller, false when it could not be
     *              acquired — the caller is expected to proceed anyway rather than fail
     *              a payment
     */
    public function acquire(string $payPalOrderId): bool;

    /**
     * Releases a lock previously acquired by the caller. Must be a no-op when the lock
     * is not held.
     *
     * @param string $payPalOrderId
     *
     * @return void
     */
    public function release(string $payPalOrderId);
}
