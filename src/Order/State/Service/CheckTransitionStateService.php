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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Service;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\Resume;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumeOrder;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalAuthorization;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalCapture;
use PrestaShop\Module\PrestashopCheckout\Order\Resume\ResumePayPalRefund;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Authorization\PayPalAuthorizationStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\PayPalRefundStatus;

class CheckTransitionStateService
{
    const STATES = [
        'PayPalOrder' => [
            PayPalOrderStatus::CREATED => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalOrderStatus::SAVED => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalOrderStatus::APPROVED => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalOrderStatus::PENDING_APPROVAL => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalOrderStatus::PAYER_ACTION_REQUIRED => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalOrderStatus::VOIDED => OrderStateConfigurationKeys::CANCELED,
            PayPalOrderStatus::COMPLETED => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
        ],
        'PayPalCapture' => [
            PayPalCaptureStatus::COMPLETED => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            PayPalCaptureStatus::PENDING => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            PayPalCaptureStatus::FAILED => OrderStateConfigurationKeys::PAYMENT_ERROR,
            PayPalCaptureStatus::REFUND => OrderStateConfigurationKeys::REFUNDED,
            PayPalCaptureStatus::PARTIALLY_REFUNDED => OrderStateConfigurationKeys::PARTIALLY_REFUNDED,
            PayPalCaptureStatus::DECLINED => OrderStateConfigurationKeys::PAYMENT_ERROR,
        ],
        'PayPalAuthorization' => [
            PayPalAuthorizationStatus::CREATED => OrderStateConfigurationKeys::WAITING_CAPTURE,
            PayPalAuthorizationStatus::CAPTURED => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            PayPalAuthorizationStatus::DENIED => OrderStateConfigurationKeys::PAYMENT_ERROR,
            PayPalAuthorizationStatus::EXPIRED => OrderStateConfigurationKeys::PAYMENT_ERROR,
            PayPalAuthorizationStatus::PARTIALLY_CAPTURED => OrderStateConfigurationKeys::PARTIALLY_PAID,
            PayPalAuthorizationStatus::VOIDED => OrderStateConfigurationKeys::CANCELED,
            PayPalAuthorizationStatus::PENDING => OrderStateConfigurationKeys::WAITING_CAPTURE,
        ],
        'PayPalRefund' => [
            PayPalRefundStatus::CANCELLED => OrderStateConfigurationKeys::CANCELED,
            PayPalRefundStatus::PENDING => OrderStateConfigurationKeys::REFUNDED,
            PayPalRefundStatus::FAILED => OrderStateConfigurationKeys::PAYMENT_ERROR,
            PayPalRefundStatus::COMPLETED => OrderStateConfigurationKeys::REFUNDED,
        ],
    ];

    /**
     * @var CheckTransitionPayPalOrderStatusService
     */
    private $checkTransitionPayPalOrderStatusService;

    /**
     * @var CheckOrderState
     */
    private $checkOrderState;

    /**
     * @var CheckOrderAmount
     */
    private $checkOrderAmount;

    /**
     * @param CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService
     * @param CheckOrderState $checkOrderState
     * @param CheckOrderAmount $checkOrderAmount
     */
    public function __construct(CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService, CheckOrderState $checkOrderState, CheckOrderAmount $checkOrderAmount)
    {
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
        $this->checkOrderState = $checkOrderState;
        $this->checkOrderAmount = $checkOrderAmount;
    }

    /**
     * @param Resume $data
     *
     * @return false|string
     *
     * @throws OrderException
     */
    public function getNewOrderState($data)
    {
        // PayPal Order Status
        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($data->getPaypalOrder()->getOldStatus(), $data->getPaypalOrder()->getNewStatus())) {
            return false;
        }

        $newOrderState = $this->getPsState($data);
        if ($this->checkOrderState->isOrderStateTransitionAvailable($data->getOrder()->getCurrentOrderStatus(), $newOrderState)) {
            return $newOrderState;
        } else {
            return false;
        }
    }

    /**
     * @param Resume $data
     *
     * @return false|string
     *
     * @throws OrderException
     */
    public function getPsState($data)
    {
        $state = false;
        switch ($data->getPaypalOrder()->getNewStatus()) {
            case PayPalOrderStatus::COMPLETED:
                if ($data->getPaypalCapture() != null) {
                    $state = $this->getPsCaptureState($data->getPaypalCapture(), $data->getOrder());
                } elseif ($data->getPaypalAuthorization() != null) {
                    $state = $this->getPsAuthorizationState($data->getPaypalAuthorization(), $data->getOrder());
                } elseif ($data->getPaypalRefund() != null) {
                    $state = $this->getPsRefundState($data->getPaypalRefund(), $data->getOrder());
                }
                break;
            default:
                $state = key_exists($data->getPaypalOrder()->getNewStatus(), self::STATES['PayPalOrder']) ? self::STATES['PayPalOrder'][$data->getPaypalOrder()->getNewStatus()] : false;
        }

        return $state;
    }

    /**
     * @param ResumePayPalCapture $paypalCapture
     * @param ResumeOrder $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsCaptureState($paypalCapture, $psOrder)
    {
        if (key_exists($paypalCapture->getStatus(), self::STATES['PayPalCapture'])) {
            $state = self::STATES['PayPalCapture'][$paypalCapture->getStatus()];
            if ($state == OrderStateConfigurationKeys::PAYMENT_ACCEPTED) {
                $totalPaid = $this->sum($paypalCapture->getAmount(), $psOrder->getTotalAmountPaid());
                switch ($this->checkOrderAmount->checkAmount($psOrder->getTotalAmount(), $totalPaid)) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PARTIALLY_PAID;
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ERROR;
                        break;
                }
            } elseif ($state == OrderStateConfigurationKeys::REFUNDED || $state == OrderStateConfigurationKeys::PARTIALLY_REFUNDED) {
                $totalRefund = $this->sum($paypalCapture->getAmount(), $psOrder->getTotalRefunded());
                switch ($this->checkOrderAmount->checkAmount($psOrder->getTotalAmount(), $totalRefund)) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PARTIALLY_REFUNDED;
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ERROR;
                        break;
                    case CheckOrderAmount::ORDER_FULL_PAID:
                        $state = OrderStateConfigurationKeys::REFUNDED;
                        break;
                }
            }
        } else {
            $state = false;
        }

        return $state;
    }

    /**
     * @param ResumePayPalAuthorization $paypalAuthorization
     * @param ResumeOrder $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsAuthorizationState($paypalAuthorization, $psOrder)
    {
        if (key_exists($paypalAuthorization->getStatus(), self::STATES['PayPalAuthorization'])) {
            $state = self::STATES['PayPalAuthorization'][$paypalAuthorization->getStatus()];
            if ($state == OrderStateConfigurationKeys::PAYMENT_ACCEPTED || $state == OrderStateConfigurationKeys::PARTIALLY_PAID) {
                $totalAuthorization = $this->sum($paypalAuthorization->getAmount(), $psOrder->getTotalAmountPaid());
                switch ($this->checkOrderAmount->checkAmount($psOrder->getTotalAmount(), $totalAuthorization)) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PARTIALLY_PAID;
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ERROR;
                        break;
                    case CheckOrderAmount::ORDER_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ACCEPTED;
                        break;
                }
            }
        } else {
            $state = false;
        }

        return $state;
    }

    /**
     * @param ResumePayPalRefund $paypalRefund
     * @param ResumeOrder $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsRefundState($paypalRefund, $psOrder)
    {
        if (key_exists($paypalRefund->getStatus(), self::STATES['PayPalRefund'])) {
            $state = self::STATES['PayPalRefund'][$paypalRefund->getStatus()];
            if ($state == OrderStateConfigurationKeys::REFUNDED || $state == OrderStateConfigurationKeys::PARTIALLY_REFUNDED) {
                $totalRefund = $this->sum($paypalRefund->getAmount(), $psOrder->getTotalRefunded());
                switch ($this->checkOrderAmount->checkAmount($psOrder->getTotalAmount(), $totalRefund)) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PARTIALLY_REFUNDED;
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ERROR;
                        break;
                    case CheckOrderAmount::ORDER_FULL_PAID:
                        $state = OrderStateConfigurationKeys::REFUNDED;
                        break;
                }
            }
        } else {
            $state = false;
        }

        return $state;
    }

    /**
     * @param string $num1
     * @param string $num2
     *
     * @return string
     */
    private function sum($num1, $num2)
    {
        return (string) (floatval($num1) + floatval($num2));
    }
}
