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

use Exception;
use Order;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;

class AbstractOrderHandler
{
    /**
     * @param OrderId $orderId
     *
     * @return Order
     *
     * @throws OrderException
     */
    protected function getOrder(OrderId $orderId)
    {
        try {
            $order = new Order($orderId->getValue());
        } catch (Exception $exception) {
            throw new OrderNotFoundException(sprintf('Error occurred when trying to get order object #%s', $orderId->getValue()), OrderNotFoundException::NOT_FOUND, $exception);
        }

        if ($order->id !== $orderId->getValue()) {
            throw new OrderNotFoundException(sprintf('Order with id "%d" was not found.', $orderId->getValue()), OrderNotFoundException::NOT_FOUND);
        }

        return $order;
    }
}
