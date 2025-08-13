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

namespace PsCheckout\Core\PayPal\Order\Action;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderPurchaseUnit;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderRefund;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderPurchaseUnitRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRefundRepositoryInterface;

class UpdatePayPalOrderPurchaseUnitAction implements UpdatePayPalOrderPurchaseUnitActionInterface
{
    /**
     * @var PayPalOrderPurchaseUnitRepositoryInterface
     */
    private $payPalOrderPurchaseUnitRepository;

    /**
     * @var PayPalOrderCaptureRepositoryInterface
     */
    private $payPalOrderCaptureRepository;

    /**
     * @var PayPalOrderAuthorizationRepositoryInterface
     */
    private $payPalOrderAuthorizationRepository;

    /**
     * @var PayPalOrderRefundRepositoryInterface
     */
    private $payPalOrderRefundRepository;

    public function __construct(
        PayPalOrderPurchaseUnitRepositoryInterface $payPalOrderPurchaseUnitRepository,
        PayPalOrderCaptureRepositoryInterface $payPalOrderCaptureRepository,
        PayPalOrderAuthorizationRepositoryInterface $payPalOrderAuthorizationRepository,
        PayPalOrderRefundRepositoryInterface $payPalOrderRefundRepository
    ) {
        $this->payPalOrderPurchaseUnitRepository = $payPalOrderPurchaseUnitRepository;
        $this->payPalOrderCaptureRepository = $payPalOrderCaptureRepository;
        $this->payPalOrderAuthorizationRepository = $payPalOrderAuthorizationRepository;
        $this->payPalOrderRefundRepository = $payPalOrderRefundRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $orderResponse)
    {
        foreach ($orderResponse->getPurchaseUnits() as $purchaseUnit) {
            $payPalPurchaseUnit = new PayPalOrderPurchaseUnit(
                $orderResponse->getId(),
                crc32(json_encode($purchaseUnit)),
                $purchaseUnit['reference_id'],
                $purchaseUnit['items']
            );

            $this->payPalOrderPurchaseUnitRepository->save($payPalPurchaseUnit);

            $this->updatePayments($purchaseUnit, $orderResponse->getId());
        }
    }

    /**
     * @param array $purchaseUnit
     * @param string $orderId
     *
     * @return void
     */
    private function updatePayments(array $purchaseUnit, string $orderId)
    {
        if (!empty($purchaseUnit['payments']['captures'])) {
            foreach ($purchaseUnit['payments']['captures'] as $capture) {
                $payPalCapture = new PayPalOrderCapture(
                    $capture['id'],
                    $orderId,
                    $capture['status'],
                    $capture['create_time'],
                    $capture['update_time'],
                    $capture['seller_protection'],
                    $capture['seller_receivable_breakdown'],
                    (bool) $capture['final_capture']
                );
                $this->payPalOrderCaptureRepository->save($payPalCapture);
            }
        }

        if (!empty($purchaseUnit['payments']['authorizations'])) {
            foreach ($purchaseUnit['payments']['authorizations'] as $authorization) {
                $payPalAuthorization = new PayPalOrderAuthorization(
                    $authorization['id'],
                    $orderId,
                    $authorization['status'],
                    $authorization['expiration_time'],
                    $authorization['seller_protection']
                );
                $this->payPalOrderAuthorizationRepository->save($payPalAuthorization);
            }
        }

        if (!empty($purchaseUnit['payments']['refunds'])) {
            foreach ($purchaseUnit['payments']['refunds'] as $refund) {
                $payPalRefund = new PayPalOrderRefund(
                    $refund['id'],
                    $orderId,
                    $refund['status'],
                    $refund['invoice_id'],
                    $refund['custom_id'],
                    $refund['acquirer_reference_number'],
                    $refund['seller_payable_breakdown']
                );
                $this->payPalOrderRefundRepository->save($payPalRefund);
            }
        }
    }
}
