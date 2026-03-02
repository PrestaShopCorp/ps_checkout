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
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;

class MerchantOrderViewPresenter
{
    /**
     * @var PayPalOrderPresenterInterface
     */
    private $payPalOrderPresenter;

    /**
     * @param PayPalOrderPresenterInterface $payPalOrderPresenter
     */
    public function __construct(PayPalOrderPresenterInterface $payPalOrderPresenter)
    {
        $this->payPalOrderPresenter = $payPalOrderPresenter;
    }

    /**
     * @param PayPalOrderResponse $paypalOrderResponse
     * @param PayPalOrder $payPalOrder
     * @param bool $isProductionEnv
     *
     * @return array{orderData: array<string, mixed>, transactionList: list<array<string, mixed>>}
     */
    public function present(
        PayPalOrderResponse $paypalOrderResponse,
        PayPalOrder $payPalOrder,
        bool $isProductionEnv
    ): array {
        $presenterData = $this->payPalOrderPresenter->present($paypalOrderResponse);

        /** @var list<array<string, mixed>> $transactions */
        $transactions = isset($presenterData['transactions']) && is_array($presenterData['transactions']) ? $presenterData['transactions'] : [];

        $currency = $this->extractCurrency($paypalOrderResponse);

        $orderData = $this->buildOrderData($presenterData, $transactions, $payPalOrder, $isProductionEnv, $currency);
        $transactionList = $this->buildTransactionList($transactions);

        return [
            'orderData' => $orderData,
            'transactionList' => $transactionList,
        ];
    }

    /**
     * @param PayPalOrderResponse $paypalOrderResponse
     *
     * @return string
     */
    private function extractCurrency(PayPalOrderResponse $paypalOrderResponse): string
    {
        foreach ($paypalOrderResponse->getPurchaseUnits() as $purchase) {
            if (!empty($purchase['amount']['currency_code'])) {
                return $purchase['amount']['currency_code'];
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $presenterData
     * @param list<array<string, mixed>> $transactions
     * @param PayPalOrder $payPalOrder
     * @param bool $isProductionEnv
     * @param string $currency
     *
     * @return array<string, mixed>
     */
    private function buildOrderData(
        array $presenterData,
        array $transactions,
        PayPalOrder $payPalOrder,
        bool $isProductionEnv,
        string $currency
    ): array {
        $isAuthorizeIntent = strtoupper((string) ($presenterData['intent'] ?? '')) === 'AUTHORIZE';

        $authTotal = 0.0;
        $captured = 0.0;

        foreach ($transactions as $tx) {
            $txType = $tx['type']['value'] ?? '';
            $txAmount = (float) $tx['amount'];

            if ($txType === 'authorization') {
                $authTotal += $txAmount;
            } elseif ($txType === 'capture') {
                $captured += $txAmount;
            }
        }

        if ($isAuthorizeIntent && $authTotal > 0) {
            $total = $authTotal;
            $leftToCapture = max(0.0, $authTotal - $captured);
        } else {
            $total = (float) $presenterData['total'];
            $captured = 0.0;
            $leftToCapture = 0.0;
        }

        $fees = (float) $presenterData['fees'];
        $balance = (float) $presenterData['balance'];

        $is3DSecureAvailable = !empty($presenterData['is3DSecureAvailable']);
        $isLiabilityShifted = !empty($presenterData['isLiabilityShifted']);
        $threeDSecure = $is3DSecureAvailable ? 'Success' : 'N/A';
        $liabilityShift = $isLiabilityShifted ? 'Bank' : 'N/A';

        $orderStatus = $presenterData['status']['translated'] ?? '';
        $paymentSourceName = $presenterData['paymentSourceName'] ?? 'PayPal';

        return [
            'reference' => 'ORDER-' . $payPalOrder->getIdCart(),
            'total' => $total,
            'currency' => $currency,
            'status' => $orderStatus,
            'balance' => $balance,
            'paymentMode' => $paymentSourceName,
            'isTestMode' => !$isProductionEnv,
            'threeDSecure' => $threeDSecure,
            'liabilityShift' => $liabilityShift,
            'financials' => [
                'gross' => $total,
                'fees' => $fees,
                'net' => $total + $fees,
                'captured' => $captured,
                'leftToCapture' => $leftToCapture,
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $transactions
     *
     * @return list<array<string, mixed>>
     */
    private function buildTransactionList(array $transactions): array
    {
        $list = [];

        foreach ($transactions as $tx) {
            $list[] = [
                'id' => $tx['id'],
                'type' => $tx['type']['translated'] ?? '',
                'status' => $tx['status']['translated'] ?? '',
                'date' => $tx['date'],
                'amount' => (float) $tx['amount'],
                'currency' => $tx['currency'],
                'reference' => $tx['id'],
                'expirationTime' => $tx['expiration_time'] ?? '',
                'isRefundable' => !empty($tx['isRefundable']),
                'maxAmountRefundable' => isset($tx['maxAmountRefundable']) ? (float) $tx['maxAmountRefundable'] : 0.0,
                'details' => [
                    'total' => (float) $tx['amount'],
                    'gross' => isset($tx['gross_amount']) ? (float) $tx['gross_amount'] : null,
                    'fee' => isset($tx['paypal_fee']) ? (float) $tx['paypal_fee'] : null,
                    'net' => isset($tx['net_amount']) ? (float) $tx['net_amount'] : null,
                    'sellerProtection' => $tx['seller_protection']['translated'] ?? '',
                ],
            ];
        }

        return $list;
    }
}
