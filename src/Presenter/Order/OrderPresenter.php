<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Order;

use PrestaShop\Module\PrestashopCheckout\Presenter\Date\DatePresenter;

class OrderPresenter
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var array
     */
    private $orderPayPal;

    /**
     * @param \Module $module
     * @param array $orderPayPal
     */
    public function __construct(\Module $module, array $orderPayPal)
    {
        $this->module = $module;
        $this->orderPayPal = $orderPayPal;
    }

    /**
     * @return array
     */
    public function present()
    {
        return [
            'id' => $this->orderPayPal['id'],
            'intent' => $this->orderPayPal['intent'],
            'status' => $this->getOrderStatus(),
            'transactions' => $this->getTransactions(),
        ];
    }

    /**
     * @return array
     */
    private function getOrderStatus()
    {
        $translated = '';
        $class = '';

        if ('CREATED' === $this->orderPayPal['status']) {
            $translated = $this->module->l('Created', 'translations');
            $class = 'info';
        }

        if ('SAVED' === $this->orderPayPal['status']) {
            $translated = $this->module->l('Saved', 'translations');
            $class = 'info';
        }

        if ('APPROVED' === $this->orderPayPal['status']) {
            $translated = $this->module->l('Approved', 'translations');
            $class = 'info';
        }

        if ('VOIDED' === $this->orderPayPal['status']) {
            $translated = $this->module->l('Voided', 'translations');
            $class = 'warning';
        }

        if ('COMPLETED' === $this->orderPayPal['status']) {
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
}
