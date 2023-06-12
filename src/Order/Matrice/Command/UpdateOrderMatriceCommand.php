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

namespace PrestaShop\Module\PrestashopCheckout\Order\Matrice\Command;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

class UpdateOrderMatriceCommand
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var PayPalOrderId
     */
    private $orderPayPalId;

    /**
     * @param int $orderId
     * @param string $orderPayPalId
     *
     * @throws OrderException
     * @throws PayPalOrderException
     */
    public function __construct($orderId, $orderPayPalId)
    {
        $this->orderId = new OrderId($orderId);
        $this->orderPayPalId = new PayPalOrderId($orderPayPalId);
    }

    /**
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return PayPalOrderId
     */
    public function getOrderPayPalId()
    {
        return $this->orderPayPalId;
    }
}
