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

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;
use PrestaShop\Module\PrestashopCheckout\Order\ValueObject\OrderId;

class GetOrderForPaymentCompletedQueryResult
{
    /**
     * @var OrderId
     */
    private $orderId;

    /**
     * @var CartId
     */
    private $cartId;

    /**
     * @var OrderStateId
     */
    private $currentStateId;

    /**
     * @var bool
     */
    private $hasBeenPaid;

    /**
     * @var string
     */
    private $totalAmount;

    /**
     * @var string
     */
    private $totalAmountPaid;

    /**
     * @var int
     */
    private $currencyId;

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * @var int|null
     */
    private $orderPaymentId;

    /**
     * @param int $orderId
     * @param int $cartId
     * @param int $currentStateId
     * @param bool $hasBeenPaid
     * @param string $totalAmount
     * @param string $totalAmountPaid
     * @param int $currencyId
     * @param string $paymentMethod
     * @param int|null $orderPaymentId
     *
     * @throws OrderException
     * @throws CartException
     * @throws OrderStateException
     */
    public function __construct(
        $orderId,
        $cartId,
        $currentStateId,
        $hasBeenPaid,
        $totalAmount,
        $totalAmountPaid,
        $currencyId,
        $paymentMethod,
        $orderPaymentId = null
    ) {
        $this->orderId = new OrderId($orderId);
        $this->cartId = new CartId($cartId);
        $this->currentStateId = new OrderStateId($currentStateId);
        $this->hasBeenPaid = $hasBeenPaid;
        $this->totalAmount = $totalAmount;
        $this->totalAmountPaid = $totalAmountPaid;
        $this->currencyId = $currencyId;
        $this->paymentMethod = $paymentMethod;
        $this->orderPaymentId = $orderPaymentId;
    }

    /**
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return CartId
     */
    public function getCartId()
    {
        return $this->cartId;
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

    /**
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @return string
     */
    public function getTotalAmountPaid()
    {
        return $this->totalAmountPaid;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return int|null
     */
    public function getOrderPaymentId()
    {
        return $this->orderPaymentId;
    }
}
