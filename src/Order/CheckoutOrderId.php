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

namespace PrestaShop\Module\PrestashopCheckout\Order;

use InvalidArgumentException;

class CheckoutOrderId
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @param int $orderId
     *
     * @throws OrderException
     */
    public function __construct($orderId)
    {
        if (!is_string($orderId)) {
            throw new InvalidArgumentException(sprintf('Invalid type - expected string, but got (%s) "%s"', gettype($number), print_r($number, true)));
        }
        if (!is_numeric($orderId)) {
            throw new InvalidArgumentException('Invalid type - expected numeric, but got (%s) "%s');
        }

        $this->assertIntegerIsGreaterThanZero((int) $orderId);

        $this->orderId = (int) $orderId;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     *
     * @throws OrderException
     */
    private function assertIntegerIsGreaterThanZero($orderId)
    {
        if (!is_int($orderId) || 0 > $orderId) {
            throw new OrderException('Order id must be greater than zero.');
        }
    }
}
