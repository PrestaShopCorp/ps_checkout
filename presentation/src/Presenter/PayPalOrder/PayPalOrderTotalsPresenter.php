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

class PayPalOrderTotalsPresenter implements PayPalOrderPresenterInterface
{
    /** {@inheritdoc} */
    public function present(PaypalOrderResponse $paypalOrderResponse): array
    {
        $total = 0.0;
        $totalRefunded = 0.0;
        $fees = 0.0;

        $currency = '';

        foreach ($paypalOrderResponse->getPurchaseUnits() as $purchase) {
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
}
