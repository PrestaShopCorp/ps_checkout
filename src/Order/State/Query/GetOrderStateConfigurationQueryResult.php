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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Query;

class GetOrderStateConfigurationQueryResult
{
    /**
     * @var int
     */
    private $canceledStateId;

    /**
     * @var int
     */
    private $paymentErrorStateId;

    /**
     * @var int
     */
    private $outOfStockUnpaidStateId;

    /**
     * @var int
     */
    private $outOfStockPaidStateId;

    /**
     * @var int
     */
    private $paymentAcceptedStateId;

    /**
     * @var int
     */
    private $refundedStateId;

    /**
     * @var int
     */
    private $authorizedStateId;

    /**
     * @var int
     */
    private $partiallyPaidStateId;

    /**
     * @var int
     */
    private $partiallyRefundedStateId;

    /**
     * @var int
     */
    private $waitingCaptureStateId;

    /**
     * @var int
     */
    private $waitingPaymentCardStateId;

    /**
     * @var int
     */
    private $waitingPaymentPayPalStateId;

    /**
     * @var int
     */
    private $waitingPaymentLocalStateId;

    /**
     * @param int $canceledStateId
     * @param int $paymentErrorStateId
     * @param int $outOfStockUnpaidStateId
     * @param int $outOfStockPaidStateId
     * @param int $paymentAcceptedStateId
     * @param int $refundedStateId
     * @param int $authorizedStateId
     * @param int $partiallyPaidStateId
     * @param int $partiallyRefundedStateId
     * @param int $waitingCaptureStateId
     * @param int $waitingPaymentCardStateId
     * @param int $waitingPaymentPayPalStateId
     * @param int $waitingPaymentLocalStateId
     */
    public function __construct(
        $canceledStateId,
        $paymentErrorStateId,
        $outOfStockUnpaidStateId,
        $outOfStockPaidStateId,
        $paymentAcceptedStateId,
        $refundedStateId,
        $authorizedStateId,
        $partiallyPaidStateId,
        $partiallyRefundedStateId,
        $waitingCaptureStateId,
        $waitingPaymentCardStateId,
        $waitingPaymentPayPalStateId,
        $waitingPaymentLocalStateId
    ) {
        $this->canceledStateId = $canceledStateId;
        $this->paymentErrorStateId = $paymentErrorStateId;
        $this->outOfStockUnpaidStateId = $outOfStockUnpaidStateId;
        $this->outOfStockPaidStateId = $outOfStockPaidStateId;
        $this->paymentAcceptedStateId = $paymentAcceptedStateId;
        $this->refundedStateId = $refundedStateId;
        $this->authorizedStateId = $authorizedStateId;
        $this->partiallyPaidStateId = $partiallyPaidStateId;
        $this->partiallyRefundedStateId = $partiallyRefundedStateId;
        $this->waitingCaptureStateId = $waitingCaptureStateId;
        $this->waitingPaymentCardStateId = $waitingPaymentCardStateId;
        $this->waitingPaymentPayPalStateId = $waitingPaymentPayPalStateId;
        $this->waitingPaymentLocalStateId = $waitingPaymentLocalStateId;
    }

    /**
     * @return int
     */
    public function getCanceledStateId()
    {
        return $this->canceledStateId;
    }

    /**
     * @return int
     */
    public function getPaymentErrorStateId()
    {
        return $this->paymentErrorStateId;
    }

    /**
     * @return int
     */
    public function getOutOfStockUnpaidStateId()
    {
        return $this->outOfStockUnpaidStateId;
    }

    /**
     * @return int
     */
    public function getOutOfStockPaidStateId()
    {
        return $this->outOfStockPaidStateId;
    }

    /**
     * @return int
     */
    public function getPaymentAcceptedStateId()
    {
        return $this->paymentAcceptedStateId;
    }

    /**
     * @return int
     */
    public function getRefundedStateId()
    {
        return $this->refundedStateId;
    }

    /**
     * @return int
     */
    public function getAuthorizedStateId()
    {
        return $this->authorizedStateId;
    }

    /**
     * @return int
     */
    public function getPartiallyPaidStateId()
    {
        return $this->partiallyPaidStateId;
    }

    /**
     * @return int
     */
    public function getPartiallyRefundedStateId()
    {
        return $this->partiallyRefundedStateId;
    }

    /**
     * @return int
     */
    public function getWaitingCaptureStateId()
    {
        return $this->waitingCaptureStateId;
    }

    /**
     * @return int
     */
    public function getWaitingPaymentCardStateId()
    {
        return $this->waitingPaymentCardStateId;
    }

    /**
     * @return int
     */
    public function getWaitingPaymentPayPalStateId()
    {
        return $this->waitingPaymentPayPalStateId;
    }

    /**
     * @return int
     */
    public function getWaitingPaymentLocalStateId()
    {
        return $this->waitingPaymentLocalStateId;
    }
}
