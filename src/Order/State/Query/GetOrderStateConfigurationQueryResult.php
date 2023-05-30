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

use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateConfiguration;

class GetOrderStateConfigurationQueryResult
{
    /**
     * @var OrderStateConfiguration
     */
    private $canceledState;

    /**
     * @var OrderStateConfiguration
     */
    private $paymentErrorState;

    /**
     * @var OrderStateConfiguration
     */
    private $outOfStockUnpaidState;

    /**
     * @var OrderStateConfiguration
     */
    private $outOfStockPaidState;

    /**
     * @var OrderStateConfiguration
     */
    private $paymentAcceptedState;

    /**
     * @var OrderStateConfiguration
     */
    private $refundedState;

    /**
     * @var OrderStateConfiguration
     */
    private $authorizedState;

    /**
     * @var OrderStateConfiguration
     */
    private $partiallyPaidState;

    /**
     * @var OrderStateConfiguration
     */
    private $partiallyRefundedState;

    /**
     * @var OrderStateConfiguration
     */
    private $waitingCaptureState;

    /**
     * @var OrderStateConfiguration
     */
    private $waitingPaymentCardState;

    /**
     * @var OrderStateConfiguration
     */
    private $waitingPaymentPayPalState;

    /**
     * @var OrderStateConfiguration
     */
    private $waitingPaymentLocalState;

    /**
     * @param OrderStateConfiguration $canceledState
     * @param OrderStateConfiguration $paymentErrorState
     * @param OrderStateConfiguration $outOfStockUnpaidState
     * @param OrderStateConfiguration $outOfStockPaidState
     * @param OrderStateConfiguration $paymentAcceptedState
     * @param OrderStateConfiguration $refundedState
     * @param OrderStateConfiguration $authorizedState
     * @param OrderStateConfiguration $partiallyPaidState
     * @param OrderStateConfiguration $partiallyRefundedState
     * @param OrderStateConfiguration $waitingCaptureState
     * @param OrderStateConfiguration $waitingPaymentCardState
     * @param OrderStateConfiguration $waitingPaymentPayPalState
     * @param OrderStateConfiguration $waitingPaymentLocalState
     */
    public function __construct(
        $canceledState,
        $paymentErrorState,
        $outOfStockUnpaidState,
        $outOfStockPaidState,
        $paymentAcceptedState,
        $refundedState,
        $authorizedState,
        $partiallyPaidState,
        $partiallyRefundedState,
        $waitingCaptureState,
        $waitingPaymentCardState,
        $waitingPaymentPayPalState,
        $waitingPaymentLocalState
    ) {
        $this->canceledState = $canceledState;
        $this->paymentErrorState = $paymentErrorState;
        $this->outOfStockUnpaidState = $outOfStockUnpaidState;
        $this->outOfStockPaidState = $outOfStockPaidState;
        $this->paymentAcceptedState = $paymentAcceptedState;
        $this->refundedState = $refundedState;
        $this->authorizedState = $authorizedState;
        $this->partiallyPaidState = $partiallyPaidState;
        $this->partiallyRefundedState = $partiallyRefundedState;
        $this->waitingCaptureState = $waitingCaptureState;
        $this->waitingPaymentCardState = $waitingPaymentCardState;
        $this->waitingPaymentPayPalState = $waitingPaymentPayPalState;
        $this->waitingPaymentLocalState = $waitingPaymentLocalState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getCanceledState()
    {
        return $this->canceledState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getPaymentErrorState()
    {
        return $this->paymentErrorState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getOutOfStockUnpaidState()
    {
        return $this->outOfStockUnpaidState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getOutOfStockPaidState()
    {
        return $this->outOfStockPaidState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getPaymentAcceptedState()
    {
        return $this->paymentAcceptedState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getRefundedState()
    {
        return $this->refundedState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getAuthorizedState()
    {
        return $this->authorizedState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getPartiallyPaidState()
    {
        return $this->partiallyPaidState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getPartiallyRefundedState()
    {
        return $this->partiallyRefundedState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getWaitingCaptureState()
    {
        return $this->waitingCaptureState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getWaitingPaymentCardState()
    {
        return $this->waitingPaymentCardState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getWaitingPaymentPayPalState()
    {
        return $this->waitingPaymentPayPalState;
    }

    /**
     * @return OrderStateConfiguration
     */
    public function getWaitingPaymentLocalState()
    {
        return $this->waitingPaymentLocalState;
    }

    /**
     * @todo Remove this
     *
     * @param $orderStateName
     *
     * @return int|false
     */
    public function getIdByKey($orderStateName)
    {
        foreach ($this as $orderState) {
            if ($orderState->getOrderStateConfigurationName() == $orderStateName) {
                return $orderState->getOrderStateId();
            }
        }

        return false;
    }
}
