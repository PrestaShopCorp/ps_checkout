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

namespace PrestaShop\Module\PrestashopCheckout\Order\Service;

use Order;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;

class CheckOrderAmount
{
    // The order was paid
    const ORDER_FULL_PAID = 1;

    // The order wasn't full paid, there are still amount to capture
    const ORDER_NOT_FULL_PAID = 2;

    // The order was over paid
    const ORDER_TO_MUCH_PAID = 3;

    /**
     * @param string $totalAmount
     * @param string $totalAmountPaid
     *
     * @return int
     *
     * @throws OrderException
     */
    public function checkAmount($totalAmount, $totalAmountPaid)
    {
        if (!is_string($totalAmount)) {
            throw new OrderException(sprintf('Type of totalAmount (%s) is not string', var_export($totalAmount, true)), OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER);
        }
        if (!is_numeric($totalAmount)) {
            throw new OrderException(sprintf('Type of totalAmount (%s) is not numeric', var_export($totalAmount, true)), OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER);
        }

        if (!is_string($totalAmountPaid)) {
            throw new OrderException(sprintf('Type of totalAmountPaid (%s) is not string', var_export($totalAmountPaid, true)), OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER);
        }
        if (!is_numeric($totalAmountPaid)) {
            throw new OrderException(sprintf('Type of totalAmountPaid (%s) is not numeric', var_export($totalAmountPaid, true)), OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER);
        }

        if ((float) $totalAmount == (float) $totalAmountPaid) {
            return static::ORDER_FULL_PAID;
        } elseif ((float) $totalAmount > (float) $totalAmountPaid) {
            return static::ORDER_NOT_FULL_PAID;
        } else {
            return static::ORDER_TO_MUCH_PAID;
        }
    }
}
