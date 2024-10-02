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

namespace PrestaShop\Module\PrestashopCheckout\Order\Query;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;

class GetOrderForPaymentRefundedQueryResult
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var OrderStateId
     */
    private $currentStateId;

    /**
     * @var bool
     */
    private $hasBeenPaid;

    /**
     * @var bool
     */
    private $hasBeenPartiallyRefund;

    /**
     * @var bool
     */
    private $hasBeenTotallyRefund;

    /**
     * @var string
     */
    private $totalAmount;

    /**
     * @var int
     */
    private $currencyId;

    /**
     * @param int $orderId
     * @param int $currentStateId
     * @param bool $hasBeenPaid
     * @param bool $hasBeenPartiallyRefund
     * @param bool $hasBeenTotallyRefund
     * @param string $totalAmount
     * @param int $currencyId
     *
     * @throws OrderException
     * @throws OrderStateException
     */
    public function __construct(
        $orderId,
        $currentStateId,
        $hasBeenPaid,
        $hasBeenPartiallyRefund,
        $hasBeenTotallyRefund,
        $totalAmount,
        $currencyId
    ) {
        $this->orderId = new OrderId($orderId);
        $this->currentStateId = new OrderStateId($currentStateId);
        $this->hasBeenPaid = $hasBeenPaid;
        $this->hasBeenPartiallyRefund = $hasBeenPartiallyRefund;
        $this->hasBeenTotallyRefund = $hasBeenTotallyRefund;
        $this->totalAmount = $totalAmount;
        $this->currencyId = $currencyId;
    }

    /**
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return OrderStateId
     */
    public function getCurrentStateId()
    {
        return $this->currentStateId;
    }

    /**
     * @return bool
     */
    public function hasBeenPaid()
    {
        return $this->hasBeenPaid;
    }

    public function hasBeenPartiallyRefund()
    {
        return $this->hasBeenPartiallyRefund;
    }

    /**
     * @return bool
     */
    public function hasBeenTotallyRefund()
    {
        return $this->hasBeenTotallyRefund;
    }

    /**
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }
}
