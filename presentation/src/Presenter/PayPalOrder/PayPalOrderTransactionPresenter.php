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
use PsCheckout\Presentation\Presenter\Date\DatePresenterInterface;
use PsCheckout\Presentation\TranslatorInterface;

class PayPalOrderTransactionPresenter implements PayPalOrderPresenterInterface
{
    /**
     * @var DatePresenterInterface
     */
    private $datePresenter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param DatePresenterInterface $datePresenter
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DatePresenterInterface $datePresenter,
        TranslatorInterface $translator
    ) {
        $this->datePresenter = $datePresenter;
        $this->translator = $translator;
    }

    /** {@inheritdoc} */
    public function present(PaypalOrderResponse $paypalOrderResponse): array
    {
        return [
            'transactions' => $this->getTransactions($paypalOrderResponse),
        ];
    }

    /**
     * @param PaypalOrderResponse $paypalOrderResponse
     *
     * @return array
     */
    private function getTransactions(PaypalOrderResponse $paypalOrderResponse): array
    {
        $transactions = [];

        foreach ($paypalOrderResponse->getPurchaseUnits() as $purchase) {
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
                        'date' => $this->datePresenter->present($refund['create_time'], 'Y-m-d H:i:s'),
                        'isRefundable' => false,
                        'maxAmountRefundable' => 0,
                        'gross_amount' => $refund['seller_payable_breakdown']['gross_amount']['value'] ?? '',
                        'paypal_fee' => $refund['seller_payable_breakdown']['paypal_fee']['value'] ?? '',
                        'net_amount' => $refund['seller_payable_breakdown']['net_amount']['value'] ?? '',
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
                        'date' => $this->datePresenter->present($payment['create_time'], 'Y-m-d H:i:s'),
                        'isRefundable' => in_array($payment['status'], ['COMPLETED', 'PARTIALLY_REFUNDED']),
                        'maxAmountRefundable' => $maxAmountRefundable > 0 ? $maxAmountRefundable : 0,
                        'gross_amount' => $payment['seller_receivable_breakdown']['gross_amount']['value'] ?? '',
                        'paypal_fee' => $payment['seller_receivable_breakdown']['paypal_fee']['value'] ?? '',
                        'net_amount' => $payment['seller_receivable_breakdown']['net_amount']['value'] ?? '',
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
     * @param string $type
     *
     * @return array
     */
    private function getTransactionType(string $type): array
    {
        switch ($type) {
            case 'capture':
                $translated = $this->translator->trans('Payment');
                $class = 'payment';

                break;
            case 'refund':
                $translated = $this->translator->trans('Refund');
                $class = 'refund';

                break;
            default:
                $translated = '';
                $class = '';
        }

        return [
            'value' => $type,
            'translated' => $translated,
            'class' => $class,
        ];
    }

    /**
     * @param string $status
     *
     * @return array
     */
    private function getTransactionStatus(string $status): array
    {
        switch ($status) {
            case 'COMPLETED':
                $translated = $this->translator->trans('Completed');
                $class = 'success';

                break;
            case 'PENDING':
                $translated = $this->translator->trans('Pending');
                $class = 'warning';

                break;
            case 'DECLINED':
                $translated = $this->translator->trans('Declined');
                $class = 'danger';

                break;
            case 'PARTIALLY_REFUNDED':
                $translated = $this->translator->trans('Partially refunded');
                $class = 'info';

                break;
            case 'REFUNDED':
                $translated = $this->translator->trans('Refunded');
                $class = 'info';

                break;
            default:
                $translated = '';
                $class = '';
        }

        return [
            'value' => $status,
            'translated' => $translated,
            'class' => $class,
        ];
    }

    /**
     * @param array $payment
     *
     * @return array
     */
    private function getSellerProtection(array $payment): array
    {
        if (empty($payment['seller_protection'])) {
            return [];
        }

        $help = [];
        $status = isset($payment['seller_protection']['status']) ? $payment['seller_protection']['status'] : '';
        $dispute_categories = isset($payment['seller_protection']['dispute_categories']) ? $this->getDisputeCategoriesValues($payment['seller_protection']['dispute_categories']) : [];

        switch ($status) {
            case 'ELIGIBLE':
                $help[] = $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.');

                if (!empty($dispute_categories)) {
                    $help[] = $this->translator->trans('Dispute categories covered:');
                    $help[] = implode(', ', $dispute_categories);
                }

                $help[] = $this->translator->trans('For more information, please go to the official PayPal website.');

                return [
                    'value' => $status,
                    'translated' => $this->translator->trans('Eligible'),
                    'help' => implode(' ', $help),
                    'class' => 'success',
                ];
            case 'PARTIALLY_ELIGIBLE':
                $help[] = $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item.');

                if (!empty($dispute_categories)) {
                    $help[] = $this->translator->trans('Dispute categories covered:');
                    $help[] = implode(', ', $dispute_categories);
                }

                $help[] = $this->translator->trans('For more information, please go to the official PayPal website.');

                return [
                    'value' => $status,
                    'translated' => $this->translator->trans('Partially eligible'),
                    'help' => implode(' ', $help),
                    'class' => 'info',
                ];
            case 'NOT_ELIGIBLE':
                $help[] = $this->translator->trans('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.');

                if (!empty($dispute_categories)) {
                    $help[] = $this->translator->trans('Dispute categories covered:');
                    $help[] = implode(', ', $dispute_categories);
                }

                $help[] = $this->translator->trans('For more information, please go to the official PayPal website.');

                return [
                    'value' => $status,
                    'translated' => $this->translator->trans('Not eligible'),
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
    private function getDisputeCategoriesValues(array $dispute_categories): array
    {
        $disputeCategories = [];

        foreach ($dispute_categories as $dispute_category) {
            switch ($dispute_category) {
                case 'ITEM_NOT_RECEIVED':
                    $disputeCategories['ITEM_NOT_RECEIVED'] = $this->translator->trans('The payer paid for an item that they did not receive.');

                    break;
                case 'UNAUTHORIZED_TRANSACTION':
                    $disputeCategories['UNAUTHORIZED_TRANSACTION'] = $this->translator->trans('The payer did not authorize the payment.');

                    break;
            }
        }

        return $disputeCategories;
    }
}
