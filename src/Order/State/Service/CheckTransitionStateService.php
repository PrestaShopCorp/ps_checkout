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
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Authorization\CheckTransitionPayPalAuthorizationStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\CheckTransitionPayPalCaptureStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\CheckTransitionPayPalRefundStatusService;
use Symfony\Component\Workflow\Event\CompletedEvent;

class CheckTransitionStateService
{
    const STATES = [
        PayPalOrderStatus::COMPLETED => [
            'capture' => [
                PayPalCaptureStatus::COMPLETED => OrderStateConfiguration::PAYMENT_ACCEPTED,
                PayPalCaptureStatus::PENDING => OrderStateConfiguration::WAITING_PAYMENT,
                PayPalCaptureStatus::FAILED => OrderStateConfiguration::PAYMENT_ERROR,
                PayPalCaptureStatus::REFUND => OrderStateConfiguration::REFUNDED,
                PayPalCaptureStatus::PARTIALLY_REFUNDED => OrderStateConfiguration::PARTIALLY_REFUNDED,
                PayPalCaptureStatus::DECLINED => OrderStateConfiguration::PAYMENT_ERROR,
            ],
            'authorization' => [],
            'refund' => [],
        ],
    ];

    /**
     * @var CheckTransitionPayPalOrderStatusService
     */
    private $checkTransitionPayPalOrderStatusService;

    /**
     * @var CheckTransitionPayPalAuthorizationStatusService
     */
    private $checkTransitionPayPalAuthorizationStatusService;

    /**
     * @var CheckTransitionPayPalCaptureStatusService
     */
    private $checkTransitionPayPalCaptureStatusService;

    /**
     * @var CheckTransitionPayPalRefundStatusService
     */
    private $checkTransitionPayPalRefundStatusService;

    /**
     * @var CheckOrderState
     */
    private $checkOrderState;

    /**
     * @param CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService
     * @param CheckTransitionPayPalAuthorizationStatusService $checkTransitionPayPalAuthorizationStatusService
     * @param CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService
     * @param CheckTransitionPayPalRefundStatusService $checkTransitionPayPalRefundStatusService
     * @param CheckOrderState $checkOrderState
     */
    public function __construct(CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService, CheckTransitionPayPalAuthorizationStatusService $checkTransitionPayPalAuthorizationStatusService, CheckTransitionPayPalCaptureStatusService $checkTransitionPayPalCaptureStatusService, CheckTransitionPayPalRefundStatusService $checkTransitionPayPalRefundStatusService, CheckOrderState $checkOrderState)
    {
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
        $this->checkTransitionPayPalAuthorizationStatusService = $checkTransitionPayPalAuthorizationStatusService;
        $this->checkTransitionPayPalCaptureStatusService = $checkTransitionPayPalCaptureStatusService;
        $this->checkTransitionPayPalRefundStatusService = $checkTransitionPayPalRefundStatusService;
        $this->checkOrderState = $checkOrderState;
        $data = [
            'cart' => [
                'amount',
            ],
            'Order' => [ // NULL si pas d'order existante à ce stade
                'currentOrderStatus',
                'totalPaid',
                'totalAmount',
                'totalRefunded'
            ],
            'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                'status',
                'amount'
            ],
            'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                'status',
                'amount'
            ],
            'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                'status',
                'amount'
            ],
            'PayPalOrder' => [
                'status',
                'old status' // Stocké dans pscheckout_cart
                'new status' // qui vient de la payload
            ]
        ];
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
     */
    public function getNewOrderState($oldOrderState,$paypalOrder,$paypalCapture,$paypalAuthorization,$paypalRefund){

        // On reçoit le status de la commande PayPal
        // On reçoit le status de la capture si elle existe
        // On reçoit le status de l'authorization si elle existe
        // On reçoit le status du refund si il existe
        // on reçoit le status actuel de la commande PrestaShop si elle existe

        // PayPal Order Status = Completed
        // PayPal Capture Status = Completed
        // Check Amount -> Cart Total = Capture Amount -> Paiement Accepté
        // Sinon -> Paiement Partiel
        // Si ce nouveau State !== Current State et si Transition autorisé
        // Return newOrderState
        // Sinon return false

        // PayPal Order Status = Completed
        // PayPal Capture Status = PENDING
        // Check Amount -> En attente de paiement
        // Si ce nouveau State !== Current State et si Transition autorisé
        // Return newOrderState
        // Sinon return false

        // PayPal Order Status = Completed
        // PayPal Capture Status = DENIED
        // Si Order existe -> Erreur de paiement -> return newOrderState
        // sinon ne rien faire -> return false

        // PayPal Order Status
        if(!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($paypalOrder->getOldStatus(),$paypalOrder->getNewStatus()))
        {
            return false;
        }

        // Se baser sur le status actuel de Capture/Authorization/Refund venant de la payload

//        // Capture status
//        if(!$this->checkTransitionPayPalCaptureStatusService->checkAvailableStatus($paypalCapture->getOldStatus(),$paypalCapture->getNewStatus()))
//        {
//            return false;
//        }
//        if(!$this->checkTransitionPayPalAuthorizationStatusService->checkAvailableStatus($paypalAuthorization->getOldStatus(),$paypalAuthorization->getNewStatus()))
//        {
//            return false;
//        }
//        if(!$this->checkTransitionPayPalRefundStatusService->checkAvailableStatus($paypalRefund->getOldStatus(),$paypalRefund->getNewStatus()))
//        {
//            return false;
//        }

        $newOrderState = $this->getPsState($oldOrderState,$paypalOrder,$paypalCapture,$paypalAuthorization,$paypalRefund);

        if($this->checkOrderState->isCurrentOrderState($oldOrderState,$newOrderState)){
           return false;
        } elseif($this->checkOrderState->isOrderStateTransitionAvailable($oldOrderState,$newOrderState)){
            return true;
        } else {

        }

    }


    public function getPsState($oldOrderState,$paypalOrder,$paypalCapture,$paypalAuthorization,$paypalRefund)
    {
        $state = $oldOrderState;
        switch ($paypalOrder->getNewStatus()){
            case 'CREATED':
                $state = 'PS_CHECKOUT_STATE_WAITING_CAPTURE';
                break;
            case 'SAVED':
                break;
        }

        return $state;
    }
}
