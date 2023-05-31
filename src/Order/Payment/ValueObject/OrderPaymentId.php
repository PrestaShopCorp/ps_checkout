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

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Order\Payment\Exception\OrderPaymentException;

class OrderPaymentId
{
    /**
     * @var int
     */
    private $orderPaymentId;

    /**
     * @param int $orderPaymentId
     *
     * @throws OrderPaymentException
     */
    public function __construct($orderPaymentId)
    {
        $this->assertIntegerIsGreaterThanZero($orderPaymentId);

        $this->orderPaymentId = $orderPaymentId;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->orderPaymentId;
    }

    /**
     * @param int $orderPaymentId
     *
     * @throws OrderPaymentException
     */
    public function assertIntegerIsGreaterThanZero($orderPaymentId)
    {
        if (!is_int($orderPaymentId) || 0 >= $orderPaymentId) {
            throw new OrderPaymentException(sprintf('Order payment id %s is invalid. Order payment id must be number that is greater than zero.', var_export($orderPaymentId, true)), OrderPaymentException::INVALID_ID);
        }
    }
}
