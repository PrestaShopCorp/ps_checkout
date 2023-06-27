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

namespace PrestaShop\Module\PrestashopCheckout\Order\Command;

use DateTimeImmutable;
use Exception;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;

class AddOrderPaymentCommand
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var DateTimeImmutable
     */
    private $paymentDate;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var string
     */
    private $paymentAmount;

    /**
     * @var int
     */
    private $paymentCurrencyId;

    /**
     * @var string|null
     */
    private $transactionId;

    /**
     * @param int $orderId
     * @param string $paymentDate
     * @param string $paymentMethod
     * @param string $paymentAmount
     * @param int $paymentCurrencyId
     * @param string|null $transactionId
     *
     * @throws OrderException
     * @throws Exception
     */
    public function __construct(
        $orderId,
        $paymentDate,
        $paymentMethod,
        $paymentAmount,
        $paymentCurrencyId,
        $transactionId = null
    ) {
        $this->orderId = new OrderId($orderId);
        $this->paymentDate = new DateTimeImmutable($paymentDate);
        $this->paymentMethod = $paymentMethod;
        $this->paymentAmount = $paymentAmount;
        $this->paymentCurrencyId = $paymentCurrencyId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return string
     */
    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    /**
     * @return int
     */
    public function getPaymentCurrencyId()
    {
        return $this->paymentCurrencyId;
    }

    /**
     * @return string|null
     */
    public function getPaymentTransactionId()
    {
        return $this->transactionId;
    }
}
