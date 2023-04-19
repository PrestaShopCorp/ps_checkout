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

class GetOrderPaymentQueryResult
{
    /** @var string */
    private $transactionId;

    /** @var string */
    private $orderReference;

    /** @var string */
    private $amount;

    /** @var string */
    private $paymentMethod;

    /** @var string */
    private $date;

    /**
     * @param string $transactionId
     * @param string $orderReference
     * @param string $amount
     * @param string $paymentMethod
     * @param string $date
     */
    public function __construct($transactionId, $orderReference, $amount, $paymentMethod, $date)
    {
        $this->transactionId = $transactionId;
        $this->orderReference = $orderReference;
        $this->amount = $amount;
        $this->paymentMethod = $paymentMethod;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return string
     */
    public function getOrderReference()
    {
        return $this->orderReference;
    }

    /**
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
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
    public function getDate()
    {
        return $this->date;
    }
}
