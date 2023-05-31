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

namespace PrestaShop\Module\PrestashopCheckout\Order\Payment\Query;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\ValueObject\PayPalCaptureId;

class GetOrderPaymentQuery
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var PayPalCaptureId
     */
    private $transactionId;

    /**
     * @param string $orderId
     * @param string $transactionId
     *
     * @throws PayPalCaptureException
     * @throws OrderException
     */
    public function __construct($orderId, $transactionId)
    {
        $this->orderId = new OrderId($orderId);
        $this->transactionId = new PayPalCaptureId($transactionId);
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
