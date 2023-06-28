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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

class SavePayPalOrderCommand
{
    /**
     * @var PayPalOrderId
     */
    private $orderPayPalId;

    /**
     * @var string
     */
    private $orderPayPalStatus;

    /**
     * @var array;
     */
    private $orderPayPal;

    /**
     * @param string $orderPayPalId
     * @param string $orderPayPalStatus
     * @param array $orderPayPal
     *
     * @throws PayPalOrderException
     */
    public function __construct($orderPayPalId, $orderPayPalStatus, array $orderPayPal)
    {
        $this->orderPayPalId = new PayPalOrderId($orderPayPalId);
        $this->orderPayPalStatus = $orderPayPalStatus;
        $this->orderPayPal = $orderPayPal;
    }

    /**
     * @return PayPalOrderId
     */
    public function getOrderPayPalId()
    {
        return $this->orderPayPalId;
    }

    /**
     * @return string
     */
    public function getOrderPaypalStatus()
    {
        return $this->orderPayPalStatus;
    }

    /**
     * @return array
     */
    public function getOrderPayPal()
    {
        return $this->orderPayPal;
    }
}
