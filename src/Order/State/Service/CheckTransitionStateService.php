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
     * Ce qu'il nous manque dans l'idéal
     * Un ou des objets qui contiennent la data
     * Exemple :
     * - PayPalCapture qui contient toutes les propriétés d'une PayPal Capture d'après la doc PayPal
     * - PayPal Authorization idem
     * - PayPal Refund idem
     * - PayPal Order idem
     * - PrestaShop Order
     *
     * Normalement on devrait avoir une resource PayPalOrder qui contiendrait PayPalCapture, PayPalAuthorization, PayPalRefund et dans notre cas CartPS et OrderPS
     *
     * array(
     * array(cart id, id_customer, total amount)
     * array(order paypal id, order paypal status)
     * array(capture id, etc...)
     * )
     */

    /**
     * Déterminer quel status de commande PrestaShop assigner ou si besoin de le changer
     * - Cart -> Order via validateOrder
     *
     * @return bool
     *
     * @throws OrderException
     */
    public function getNewOrderState($data)
    {
        // PayPal Order Status
        if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($data['PayPalOrder']['oldStatus'], $data['PayPalOrder']['newStatus'])) {
            return false;
        }

        $newOrderState = $this->getPsState($data);
        if ($this->checkOrderState->isOrderStateTransitionAvailable($data['Order']['currentOrderStatus'], $newOrderState)) {
            return $newOrderState;
        } else {
            return false;
        }
    }

    /**
     * @param array $data
     *
     * @return false|string
     *
     * @throws OrderException
     */
    public function getPsState($data)
    {
        $state = false;
        switch ($data['PayPalOrder']['newStatus']) {
            case PayPalOrderStatus::COMPLETED:
                if (isset($data['PayPalCapture'])) {
                    $state = $this->getPsCaptureState($data['PayPalCapture'], $data['Order']);
                } elseif (isset($data['PayPalAuthorization'])) {
                    $state = $this->getPsAuthorizationState($data['PayPalAuthorization'], $data['Order']);
                } elseif (isset($data['PayPalRefund'])) {
                    $state = $this->getPsRefundState($data['PayPalRefund'], $data['Order']);
                }
                break;
            default:
                $state = key_exists($data['PayPalOrder']['newStatus'], self::STATES['PayPalOrder']) ? self::STATES['PayPalOrder'][$data['PayPalOrder']['newStatus']] : false;
        }

        return $state;
    }

    /**
     * @param array $paypalCapture
     * @param array $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsCaptureState($paypalCapture, $psOrder)
    {
        if (key_exists($paypalCapture['status'], self::STATES['PayPalCapture'])) {
            $state = self::STATES['PayPalCapture'][$paypalCapture['status']];
            if ($state == OrderStateConfigurationKeys::PAYMENT_ACCEPTED) {
                $totalPaid = (string) ($paypalCapture['amount'] + $psOrder['totalAmountPaid']);
                switch ($this->checkOrderAmount->checkAmount($psOrder['totalAmount'], $totalPaid)) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $state = OrderStateConfigurationKeys::PARTIALLY_PAID;
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $state = OrderStateConfigurationKeys::PAYMENT_ERROR;
                        break;
                }
            } elseif ($state == OrderStateConfigurationKeys::REFUNDED || $state == OrderStateConfigurationKeys::PARTIALLY_REFUNDED) {
                $totalRefund = (string) ($paypalCapture['amount'] + $psOrder['totalRefunded']);
                switch ($this->checkOrderAmount->checkAmount($psOrder['totalAmount'], $totalRefund)) {
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
     * @param array $paypalRefund
     * @param array $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsRefundState($paypalRefund, $psOrder)
    {
        if (key_exists($paypalRefund['status'], self::STATES['PayPalRefund'])) {
            $state = self::STATES['PayPalRefund'][$paypalRefund['status']];
            if ($state == OrderStateConfigurationKeys::REFUNDED || $state == OrderStateConfigurationKeys::PARTIALLY_REFUNDED) {
                $totalRefund = (string) ($paypalRefund['amount'] + $psOrder['totalRefunded']);
                switch ($this->checkOrderAmount->checkAmount($psOrder['totalAmount'], $totalRefund)) {
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
     * @param array $paypalAuthorization
     * @param array $psOrder
     *
     * @return false|mixed|string
     *
     * @throws OrderException
     */
    private function getPsAuthorizationState($paypalAuthorization, $psOrder)
    {
        if (key_exists($paypalAuthorization['status'], self::STATES['PayPalAuthorization'])) {
            $state = self::STATES['PayPalAuthorization'][$paypalAuthorization['status']];
            if ($state == OrderStateConfigurationKeys::PAYMENT_ACCEPTED || $state == OrderStateConfigurationKeys::PARTIALLY_PAID) {
                $totalAuthorization = (string) ($paypalAuthorization['amount'] + $psOrder['totalPaid']);
                switch ($this->checkOrderAmount->checkAmount($psOrder['totalAmount'], $totalAuthorization)) {
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
}
