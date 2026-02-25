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
     * @return array{orderData: array, transactionList: array}
     */
    public function present(
        PayPalOrderResponse $paypalOrderResponse,
        PayPalOrder $payPalOrder,
        bool $isProductionEnv
    ): array {
        $presenterData = $this->payPalOrderPresenter->present($paypalOrderResponse);

        $transactions = isset($presenterData['transactions']) ? $presenterData['transactions'] : [];

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
     * @param array $presenterData
     * @param array $transactions
     * @param PayPalOrder $payPalOrder
     * @param bool $isProductionEnv
     * @param string $currency
     *
     * @return array
     */
    private function buildOrderData(
        array $presenterData,
        array $transactions,
        PayPalOrder $payPalOrder,
        bool $isProductionEnv,
        string $currency
    ): array {
        $isAuthorizeIntent = strtoupper($presenterData['intent'] ?? '') === 'AUTHORIZE';

        $authTotal = 0.0;
        $captured = 0.0;

        foreach ($transactions as $tx) {
            $txType = isset($tx['type']['value']) ? $tx['type']['value'] : '';
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
        $threeDSecure = $is3DSecureAvailable ? 'Success' : 'None';
        $liabilityShift = $isLiabilityShifted ? 'Bank' : 'None';

        $orderStatus = isset($presenterData['status']['translated']) ? $presenterData['status']['translated'] : '';
        $paymentSourceName = isset($presenterData['paymentSourceName']) ? $presenterData['paymentSourceName'] : 'PayPal';

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
     * @param array $transactions
     *
     * @return array
     */
    private function buildTransactionList(array $transactions): array
    {
        $list = [];

        foreach ($transactions as $tx) {
            $list[] = [
                'id' => $tx['id'],
                'type' => isset($tx['type']['translated']) ? $tx['type']['translated'] : '',
                'status' => isset($tx['status']['translated']) ? $tx['status']['translated'] : '',
                'date' => $tx['date'],
                'amount' => (float) $tx['amount'],
                'currency' => $tx['currency'],
                'reference' => $tx['id'],
                'details' => [
                    'total' => (float) $tx['amount'],
                    'sellerProtection' => isset($tx['seller_protection']['translated']) ? $tx['seller_protection']['translated'] : '',
                ],
            ];
        }

        return $list;
    }
}
