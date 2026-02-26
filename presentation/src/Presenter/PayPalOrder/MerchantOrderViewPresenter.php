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

namespace PsCheckout\Presentation\Presenter\PayPalOrder;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;

class MerchantOrderViewPresenter
{
    /**
     * @param PayPalOrderResponse $paypalOrderResponse
     * @param bool $isProductionEnv
     *
     * @return array{order: array, transactionActions: array<string, array<string, mixed>>, isTestMode: bool}
     */
    public function present(
        PayPalOrderResponse $paypalOrderResponse,
        bool $isProductionEnv
    ): array {
        return [
            'order' => $paypalOrderResponse->toArray(),
            'transactionActions' => $this->buildTransactionActionsMap($paypalOrderResponse),
            'isTestMode' => !$isProductionEnv,
        ];
    }

    /**
     * @param PayPalOrderResponse $paypalOrderResponse
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildTransactionActionsMap(PayPalOrderResponse $paypalOrderResponse): array
    {
        /** @var array<string, array<string, mixed>> $actions */
        $actions = [];
        $purchaseUnits = $paypalOrderResponse->getPurchaseUnits();

        if (empty($purchaseUnits) || !isset($purchaseUnits[0]['payments'])) {
            return $actions;
        }

        $payments = $purchaseUnits[0]['payments'];

        $capturedTotal = 0.0;
        if (!empty($payments['captures'])) {
            foreach ($payments['captures'] as $capture) {
                /** @var string $captureStatus */
                $captureStatus = isset($capture['status']) ? (string) $capture['status'] : '';
                if (in_array($captureStatus, ['COMPLETED', 'PARTIALLY_REFUNDED', 'PENDING'], true)) {
                    $capturedTotal += isset($capture['amount']['value']) ? (float) $capture['amount']['value'] : 0.0;
                }
            }
        }

        if (!empty($payments['authorizations'])) {
            foreach ($payments['authorizations'] as $authorization) {
                $status = $authorization['status'];
                $id = $authorization['id'];
                $amount = (float) $authorization['amount']['value'];

                switch ($status) {
                    case 'CREATED':
                    case 'PENDING':
                        $actions[$id] = [
                            'capture' => $amount,
                            'void' => true,
                            'reauthorize' => true,
                        ];

                        break;
                    case 'PARTIALLY_CAPTURED':
                        $leftToCapture = max(0.0, $amount - $capturedTotal);
                        $actions[$id] = [
                            'capture' => $leftToCapture,
                            'void' => true,
                            'reauthorize' => true,
                        ];

                        break;
                }
            }
        }

        if (!empty($payments['captures'])) {
            foreach ($payments['captures'] as $capture) {
                /** @var string $status */
                $status = isset($capture['status']) ? (string) $capture['status'] : '';
                /** @var string $id */
                $id = isset($capture['id']) ? (string) $capture['id'] : '';
                $captureAmount = isset($capture['amount']['value']) ? (float) $capture['amount']['value'] : 0.0;

                if (!in_array($status, ['COMPLETED', 'PARTIALLY_REFUNDED'], true)) {
                    continue;
                }

                $refundedTotal = 0.0;
                if (!empty($payments['refunds'])) {
                    foreach ($payments['refunds'] as $refund) {
                        /** @var string $refundStatus */
                        $refundStatus = isset($refund['status']) ? (string) $refund['status'] : '';
                        if (in_array($refundStatus, ['COMPLETED', 'PENDING'], true)) {
                            $refundedTotal += isset($refund['amount']['value']) ? (float) $refund['amount']['value'] : 0.0;
                        }
                    }
                }

                $maxRefundable = max(0.0, $captureAmount - $refundedTotal);
                if ($maxRefundable > 0) {
                    $actions[$id] = [
                        'refund' => $maxRefundable,
                    ];
                }
            }
        }

        return $actions;
    }
}
