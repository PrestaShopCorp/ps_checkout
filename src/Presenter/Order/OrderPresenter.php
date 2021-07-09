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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Order;

use Module;
use PrestaShop\Module\PrestashopCheckout\Presenter\Date\DatePresenter;
use PsCheckoutCart;

class OrderPresenter
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var array
     */
    private $orderPayPal;

    /**
     * @param Module $module
     * @param array $orderPayPal
     */
    public function __construct(Module $module, array $orderPayPal)
    {
        $this->module = $module;
        $this->orderPayPal = $orderPayPal;
    }

    /**
     * @return array
     */
    public function present()
    {
        return array_merge(
            [
                'id' => $this->orderPayPal['id'],
                'intent' => $this->orderPayPal['intent'],
                'status' => $this->getOrderStatus(),
                'transactions' => $this->getTransactions(),
            ],
            $this->getOrderTotals()
        );
    }

    /**
     * @return array
     */
    private function getOrderStatus()
    {
        $translated = '';
        $class = '';

        if (PsCheckoutCart::STATUS_CREATED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Created', 'translations');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_SAVED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Saved', 'translations');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_APPROVED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Approved', 'translations');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_VOIDED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Voided', 'translations');
            $class = 'warning';
        }

        if (PsCheckoutCart::STATUS_COMPLETED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Completed', 'translations');
            $class = 'success';
        }

        return [
            'value' => $this->orderPayPal['status'],
            'translated' => $translated,
            'class' => $class,
        ];
    }

    /**
     * @return array
     */
    private function getTransactions()
    {
        if (empty($this->orderPayPal['purchase_units'])) {
            return [];
        }

        $transactions = [];

        foreach ($this->orderPayPal['purchase_units'] as $purchase) {
            if (empty($purchase['payments'])) {
                continue;
            }

            $totalRefunded = 0;

            if (!empty($purchase['payments']['refunds'])) {
                foreach ($purchase['payments']['refunds'] as $refund) {
                    $totalRefunded += $refund['amount']['value'];
                    $transactions[] = [
                        'type' => $this->getTransactionType('refund'),
                        'id' => $refund['id'],
                        'status' => $this->getTransactionStatus($refund['status']),
                        'amount' => $refund['amount']['value'],
                        'currency' => $refund['amount']['currency_code'],
                        'date' => (new DatePresenter($refund['create_time'], 'Y-m-d H:i:s'))->present(),
                        'isRefundable' => false,
                        'maxAmountRefundable' => 0,
                        'gross_amount' => $refund['seller_payable_breakdown']['gross_amount']['value'],
                        'paypal_fee' => $refund['seller_payable_breakdown']['paypal_fee']['value'],
                        'net_amount' => $refund['seller_payable_breakdown']['net_amount']['value'],
                    ];
                }
            }

            if (!empty($purchase['payments']['captures'])) {
                foreach ($purchase['payments']['captures'] as $payment) {
                    $maxAmountRefundable = $payment['amount']['value'] - $totalRefunded;
                    $transactions[] = [
                        'type' => $this->getTransactionType('capture'),
                        'id' => $payment['id'],
                        'status' => $this->getTransactionStatus($payment['status']),
                        'amount' => $payment['amount']['value'],
                        'currency' => $payment['amount']['currency_code'],
                        'date' => (new DatePresenter($payment['create_time'], 'Y-m-d H:i:s'))->present(),
                        'isRefundable' => in_array($payment['status'], ['COMPLETED', 'PARTIALLY_REFUNDED']),
                        'maxAmountRefundable' => $maxAmountRefundable > 0 ? $maxAmountRefundable : 0,
                        'gross_amount' => $payment['seller_receivable_breakdown']['gross_amount']['value'],
                        'paypal_fee' => $payment['seller_receivable_breakdown']['paypal_fee']['value'],
                        'net_amount' => $payment['seller_receivable_breakdown']['net_amount']['value'],
                    ];
                }
            }
        }

        if (!empty($transactions)) {
            uasort($transactions, function (array $transactionA, array $transactionB) {
                return strtotime($transactionB['date']) - strtotime($transactionA['date']);
            });
        }

        return $transactions;
    }

    /**
     * @param string $status
     *
     * @return array
     */
    private function getTransactionStatus($status)
    {
        $translated = '';
        $class = '';

        if ('COMPLETED' === $status) {
            $translated = $this->module->l('Completed', 'translations');
            $class = 'success';
        }

        if ('PENDING' === $status) {
            $translated = $this->module->l('Pending', 'translations');
            $class = 'warning';
        }

        if ('DECLINED' === $status) {
            $translated = $this->module->l('Declined', 'translations');
            $class = 'danger';
        }

        if ('PARTIALLY_REFUNDED' === $status) {
            $translated = $this->module->l('Partially refunded', 'translations');
            $class = 'info';
        }

        if ('REFUNDED' === $status) {
            $translated = $this->module->l('Refunded', 'translations');
            $class = 'info';
        }

        return [
            'value' => $status,
            'translated' => $translated,
            'class' => $class,
        ];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    private function getTransactionType($type)
    {
        $translated = '';
        $class = '';

        if ('capture' === $type) {
            $translated = $this->module->l('Payment', 'translations');
            $class = 'payment';
        }

        if ('refund' === $type) {
            $translated = $this->module->l('Refund', 'translations');
            $class = 'refund';
        }

        return [
            'value' => $type,
            'translated' => $translated,
            'class' => $class,
        ];
    }

    private function getTotal()
    {
        if (empty($this->orderPayPal['purchase_units'])) {
            return '0';
        }

        $total = 0.0;
        $currency = '';

        foreach ($this->orderPayPal['purchase_units'] as $purchase) {
            if (empty($purchase['payments'])) {
                continue;
            }

            $total += (float) $purchase['amount']['value'];
            $currency = $purchase['amount']['currency_code'];
        }

        return number_format($total, 2) . " $currency";
    }

    private function getBalance()
    {
        if (empty($this->orderPayPal['purchase_units'])) {
            return '0';
        }

        $balance = 0.0;
        $totalRefunded = 0.0;
        $currency = '';

        foreach ($this->orderPayPal['purchase_units'] as $purchase) {
            if (empty($purchase['payments'])) {
                continue;
            }

            $currency = $purchase['amount']['currency_code'];

            if (!empty($purchase['payments']['refunds'])) {
                foreach ($purchase['payments']['refunds'] as $refund) {
                    $totalRefunded += $refund['amount']['value'];
                }
            }

            if (!empty($purchase['payments']['captures'])) {
                foreach ($purchase['payments']['captures'] as $payment) {
                    $balance += $payment['amount']['value'];
                    $balance -= $payment['seller_receivable_breakdown']['paypal_fee']['value'];
                }
            }
        }

        return number_format($balance - $totalRefunded, 2) . " $currency";
    }

    /**
     * returns order total, balance and fees.
     * Added into one function because they all require same foreach
     *
     * @return array
     */
    private function getOrderTotals()
    {
        $total = 0.0;
        $balance = 0.0;
        $totalRefunded = 0.0;
        $fees = 0.0;

        $currency = '';

        foreach ($this->orderPayPal['purchase_units'] as $purchase) {
            if (empty($purchase['payments'])) {
                continue;
            }

            $currency = $purchase['amount']['currency_code'];

            if (!empty($purchase['payments']['refunds'])) {
                foreach ($purchase['payments']['refunds'] as $refund) {
                    $totalRefunded += $refund['amount']['value'];
                }
            }

            if (!empty($purchase['payments']['captures'])) {
                foreach ($purchase['payments']['captures'] as $payment) {
                    $total += $payment['amount']['value'];
                    $fees -= $payment['seller_receivable_breakdown']['paypal_fee']['value'];
                }
            }
        }

        return [
            'total' => number_format($total, 2) . " $currency",
            'fees' => number_format($fees, 2) . " $currency",
            'balance' => number_format($total - $totalRefunded + $fees, 2) . " $currency",
        ];
    }
}
