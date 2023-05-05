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

namespace PrestaShop\Module\PrestashopCheckout\Order\Resume;

use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;

class ResumeOrder
{
    /**
     * @var string
     */
    public $currentOrderStatus;

    /**
     * @var OrderStateId
     */
    public $currentOrderStatusId;

    /**
     * @var string
     */
    public $totalAmountPaid;

    /**
     * @var string
     */
    public $totalAmount;

    /**
     * @var string
     */
    public $totalRefunded;

    /**
     * @param string $currentOrderStatus
     * @param OrderStateId $currentOrderStatusId
     * @param string $totalAmountPaid
     * @param string $totalAmount
     * @param string $totalRefunded
     */
    public function __construct($currentOrderStatus, OrderStateId $currentOrderStatusId, $totalAmountPaid, $totalAmount, $totalRefunded)
    {
        $this->currentOrderStatus = $currentOrderStatus;
        $this->currentOrderStatusId = $currentOrderStatusId;
        $this->totalAmountPaid = $totalAmountPaid;
        $this->totalAmount = $totalAmount;
        $this->totalRefunded = $totalRefunded;
    }

    /**
     * @return string
     */
    public function getCurrentOrderStatus()
    {
        return $this->currentOrderStatus;
    }

    /**
     * @return OrderStateId
     */
    public function getCurrentOrderStatusId()
    {
        return $this->currentOrderStatusId;
    }

    /**
     * @return string
     */
    public function getTotalAmountPaid()
    {
        return $this->totalAmountPaid;
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
    public function getTotalRefunded()
    {
        return $this->totalRefunded;
    }
}
