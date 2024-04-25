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

use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;
use PrestaShop\Module\PrestashopCheckout\Presenter\Date\DatePresenter;
use PrestaShop\Module\PrestashopCheckout\Provider\PaymentMethodLogoProvider;
use Ps_checkout;
use PsCheckoutCart;

class OrderPresenter
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var array
     */
    private $orderPayPal;
    /**
     * @var FundingSourceTranslationProvider
     */
    private $fundingSourceTranslationProvider;

    /**
     * @param Ps_checkout $module
     * @param array $orderPayPal
     */
    public function __construct(Ps_checkout $module, array $orderPayPal)
    {
        $this->module = $module;
        $this->orderPayPal = $orderPayPal;
        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService(FundingSourceTranslationProvider::class);
        $this->fundingSourceTranslationProvider = $fundingSourceTranslationProvider;
    }

    /**
     * @return array
     */
    public function present()
    {
        if (empty($this->orderPayPal)) {
            return [];
        }

        $card3DSecure = new Card3DSecure();

        return array_merge(
            [
                'id' => $this->orderPayPal['id'],
                'intent' => $this->orderPayPal['intent'],
                'status' => $this->getOrderStatus(),
                'transactions' => $this->getTransactions(),
                'is3DSecureAvailable' => $card3DSecure->is3DSecureAvailable($this->orderPayPal),
                'isLiabilityShifted' => $card3DSecure->isLiabilityShifted($this->orderPayPal),
                'paymentSource' => $this->getPaymentSourceName($this->orderPayPal),
                'paymentSourceLogo' => $this->getPaymentSourceLogo($this->orderPayPal),
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
            $translated = $this->module->l('Created', 'orderpresenter');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_SAVED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Saved', 'orderpresenter');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_APPROVED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Approved', 'orderpresenter');
            $class = 'info';
        }

        if (PsCheckoutCart::STATUS_VOIDED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Voided', 'orderpresenter');
            $class = 'warning';
        }

        if (PsCheckoutCart::STATUS_COMPLETED === $this->orderPayPal['status']) {
            $translated = $this->module->l('Completed', 'orderpresenter');
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
                        'gross_amount' => isset($refund['seller_payable_breakdown']['gross_amount']['value']) ? $refund['seller_payable_breakdown']['gross_amount']['value'] : '',
                        'paypal_fee' => isset($refund['seller_payable_breakdown']['paypal_fee']['value']) ? $refund['seller_payable_breakdown']['paypal_fee']['value'] : '',
                        'net_amount' => isset($refund['seller_payable_breakdown']['net_amount']['value']) ? $refund['seller_payable_breakdown']['net_amount']['value'] : '',
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
                        'gross_amount' => isset($payment['seller_receivable_breakdown']['gross_amount']['value']) ? $payment['seller_receivable_breakdown']['gross_amount']['value'] : '',
                        'paypal_fee' => isset($payment['seller_receivable_breakdown']['paypal_fee']['value']) ? $payment['seller_receivable_breakdown']['paypal_fee']['value'] : '',
                        'net_amount' => isset($payment['seller_receivable_breakdown']['net_amount']['value']) ? $payment['seller_receivable_breakdown']['net_amount']['value'] : '',
                        'seller_protection' => $this->getSellerProtection($payment),
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
            $translated = $this->module->l('Completed', 'orderpresenter');
            $class = 'success';
        }

        if ('PENDING' === $status) {
            $translated = $this->module->l('Pending', 'orderpresenter');
            $class = 'warning';
        }

        if ('DECLINED' === $status) {
            $translated = $this->module->l('Declined', 'orderpresenter');
            $class = 'danger';
        }

        if ('PARTIALLY_REFUNDED' === $status) {
            $translated = $this->module->l('Partially refunded', 'orderpresenter');
            $class = 'info';
        }

        if ('REFUNDED' === $status) {
            $translated = $this->module->l('Refunded', 'orderpresenter');
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
            $translated = $this->module->l('Payment', 'orderpresenter');
            $class = 'payment';
        }

        if ('refund' === $type) {
            $translated = $this->module->l('Refund', 'orderpresenter');
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
                    if (isset($payment['seller_receivable_breakdown']['paypal_fee']['value'])) {
                        $balance -= $payment['seller_receivable_breakdown']['paypal_fee']['value'];
                    }
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
                    if (isset($payment['seller_receivable_breakdown']['paypal_fee']['value'])) {
                        $fees -= $payment['seller_receivable_breakdown']['paypal_fee']['value'];
                    }
                }
            }
        }

        return [
            'total' => number_format($total, 2) . " $currency",
            'fees' => number_format($fees, 2) . " $currency",
            'balance' => number_format($total - $totalRefunded + $fees, 2) . " $currency",
        ];
    }

    /**
     * @param array $orderPayPal
     *
     * @return string
     */
    private function getPaymentSourceName(array $orderPayPal)
    {
        if (isset($orderPayPal['payment_source'])) {
            return $this->fundingSourceTranslationProvider->getPaymentMethodName(key($orderPayPal['payment_source']));
        }

        return '';
    }

    /**
     * @param array $orderPayPal
     *
     * @return string
     */
    private function getPaymentSourceLogo(array $orderPayPal)
    {
        if (isset($orderPayPal['payment_source'])) {
            return (new PaymentMethodLogoProvider($this->module))->getLogoByPaymentSource($orderPayPal['payment_source']);
        }

        return '';
    }

    /**
     * @param array $payment
     *
     * @return array
     */
    private function getSellerProtection(array $payment)
    {
        if (empty($payment['seller_protection'])) {
            return [];
        }

        $help = [];
        $status = isset($payment['seller_protection']['status']) ? $payment['seller_protection']['status'] : '';
        $dispute_categories = isset($payment['seller_protection']['dispute_categories']) ? $this->getDisputeCategoriesValues($payment['seller_protection']['dispute_categories']) : [];

        switch ($status) {
            case 'ELIGIBLE':
                $help[] = $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.', 'orderpresenter');
                if (!empty($dispute_categories)) {
                    $help[] = $this->module->l('Dispute categories covered:', 'orderpresenter');
                    $help[] = implode(', ', $dispute_categories);
                }
                $help[] = $this->module->l('For more information, please go to the official PayPal website.', 'orderpresenter');

                return [
                    'value' => $status,
                    'translated' => $this->module->l('Eligible', 'orderpresenter'),
                    'help' => implode(' ', $help),
                    'class' => 'success',
                ];
            case 'PARTIALLY_ELIGIBLE':
                $help[] = $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item.', 'orderpresenter');
                if (!empty($dispute_categories)) {
                    $help[] = $this->module->l('Dispute categories covered:', 'orderpresenter');
                    $help[] = implode(', ', $dispute_categories);
                }
                $help[] = $this->module->l('For more information, please go to the official PayPal website.', 'orderpresenter');

                return [
                    'value' => $status,
                    'translated' => $this->module->l('Partially eligible', 'orderpresenter'),
                    'help' => implode(' ', $help),
                    'class' => 'info',
                ];
            case 'NOT_ELIGIBLE':
                $help[] = $this->module->l('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.', 'orderpresenter');
                if (!empty($dispute_categories)) {
                    $help[] = $this->module->l('Dispute categories covered:', 'orderpresenter');
                    $help[] = implode(', ', $dispute_categories);
                }
                $help[] = $this->module->l('For more information, please go to the official PayPal website.', 'orderpresenter');

                return [
                    'value' => $status,
                    'translated' => $this->module->l('Not eligible', 'orderpresenter'),
                    'help' => implode(' ', $help),
                    'class' => 'warning',
                ];
            default:
                return [
                    'value' => $status,
                    'translated' => $status,
                    'help' => $status,
                    'class' => 'info',
                ];
        }
    }

    /**
     * @param array $dispute_categories
     *
     * @return array
     */
    private function getDisputeCategoriesValues(array $dispute_categories)
    {
        $disputeCategories = [];

        foreach ($dispute_categories as $dispute_category) {
            switch ($dispute_category) {
                case 'ITEM_NOT_RECEIVED':
                    $disputeCategories['ITEM_NOT_RECEIVED'] = $this->module->l('The payer paid for an item that they did not receive.', 'orderpresenter');
                    break;
                case 'UNAUTHORIZED_TRANSACTION':
                    $disputeCategories['UNAUTHORIZED_TRANSACTION'] = $this->module->l('The payer did not authorize the payment.', 'orderpresenter');
                    break;
            }
        }

        return $disputeCategories;
    }
}
