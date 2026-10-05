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

namespace PsCheckout\Infrastructure\Lock;

use PsCheckout\Core\PayPal\Order\Lock\PayPalOrderLockInterface;

/**
 * Named MySQL lock (GET_LOCK / RELEASE_LOCK), scoped to the current connection,
 * so it works across PHP-FPM workers and even across web nodes sharing the database.
 * The lock is released automatically when the connection closes.
 */
class DatabasePayPalOrderLock implements PayPalOrderLockInterface
{
    const LOCK_NAME_PREFIX = 'ps_checkout_paypal_order_';

    /**
     * Seconds to wait for the other request. It has to cover a full
     * PaymentModule::validateOrder() run, which can take several seconds on
     * shops with heavy actionValidateOrder / actionOrderStatusUpdate hooks.
     */
    const LOCK_TIMEOUT = 30;

    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function acquire(string $payPalOrderId): bool
    {
        try {
            $acquired = $this->db->getValue(
                'SELECT GET_LOCK(\'' . pSQL($this->getLockName($payPalOrderId)) . '\', ' . (int) self::LOCK_TIMEOUT . ')',
                false
            );
        } catch (\Exception $exception) {
            return false;
        }

        return (int) $acquired === 1;
    }

    /**
     * {@inheritdoc}
     */
    public function release(string $payPalOrderId)
    {
        try {
            $this->db->getValue(
                'SELECT RELEASE_LOCK(\'' . pSQL($this->getLockName($payPalOrderId)) . '\')',
                false
            );
        } catch (\Exception $exception) {
            // Nothing to do: the lock is released when the connection closes anyway.
        }
    }

    /**
     * MySQL lock names are limited to 64 characters; PayPal order ids are 17.
     *
     * @param string $payPalOrderId
     *
     * @return string
     */
    private function getLockName(string $payPalOrderId): string
    {
        return substr(self::LOCK_NAME_PREFIX . $payPalOrderId, 0, 64);
    }
}
