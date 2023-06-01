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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

class GetCurrentPayPalOrderStatusQueryResult
{
    /**
     * @var PayPalOrderId
     */
    private $payPalOrderId;

    /**
     * @var string
     */
    private $payPalOrderStatus;

    /**
     * @param string $payPalOrderId
     * @param string $payPalOrderStatus
     *
     * @throws PayPalOrderException
     */
    public function __construct($payPalOrderId, $payPalOrderStatus)
    {
        $this->payPalOrderId = new PayPalOrderId($payPalOrderId);
        $this->payPalOrderStatus = $payPalOrderStatus;
    }

    /**
     * @return PayPalOrderId
     */
    public function getPayPalOrderId()
    {
        return $this->payPalOrderId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->payPalOrderStatus;
    }
}
