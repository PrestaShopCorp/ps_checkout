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

namespace PsCheckout\Core\PayPal\Refund\ValueObject;

class PayPalRefundOrder
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @var int
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
     */
    public function __construct(
        int $orderId,
        int $currentStateId,
        bool $hasBeenPaid,
        bool $hasBeenPartiallyRefund,
        bool $hasBeenTotallyRefund,
        string $totalAmount,
        int $currencyId
    ) {
        $this->orderId = $orderId;
        $this->currentStateId = $currentStateId;
        $this->hasBeenPaid = $hasBeenPaid;
        $this->hasBeenPartiallyRefund = $hasBeenPartiallyRefund;
        $this->hasBeenTotallyRefund = $hasBeenTotallyRefund;
        $this->totalAmount = $totalAmount;
        $this->currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return int
     */
    public function getCurrentStateId(): int
    {
        return $this->currentStateId;
    }

    /**
     * @return bool
     */
    public function hasBeenPaid(): bool
    {
        return $this->hasBeenPaid;
    }

    public function hasBeenPartiallyRefund(): bool
    {
        return $this->hasBeenPartiallyRefund;
    }

    /**
     * @return bool
     */
    public function hasBeenTotallyRefund(): bool
    {
        return $this->hasBeenTotallyRefund;
    }

    /**
     * @return string
     */
    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }
}
