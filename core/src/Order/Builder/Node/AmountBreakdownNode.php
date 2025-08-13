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

namespace PsCheckout\Core\Order\Builder\Node;

use PsCheckout\Utility\Common\NumberUtility;
use PsCheckout\Utility\Common\StringUtility;

class AmountBreakdownNode implements AmountBreakdownNodeInterface
{
    /**
     * @var array
     */
    private $cart;

    /**
     * {@inheritDoc}
     */
    public function build(): array
    {
        $node = [];
        $currencyIsoCode = $this->cart['currency']['iso_code'];

        $amountTotal = $this->cart['cart']['totals']['total_including_tax']['amount'];
        $breakdownItemTotal = 0;
        $breakdownTaxTotal = 0;
        $breakdownShipping = $this->cart['cart']['shipping_cost'];
        $breakdownHandling = 0;
        $breakdownDiscount = 0;

        foreach ($this->cart['products'] as $product => $value) {
            $sku = '';
            $totalWithoutTax = $value['total'];
            $totalWithTax = $value['total_wt'];
            $totalTax = $totalWithTax - $totalWithoutTax;
            $quantity = $value['quantity'];
            $unitPriceWithoutTax = NumberUtility::formatAmount($totalWithoutTax / $quantity, $currencyIsoCode);
            $unitTax = NumberUtility::formatAmount($totalTax / $quantity, $currencyIsoCode);
            $breakdownItemTotal += $unitPriceWithoutTax * $quantity;
            $breakdownTaxTotal += $unitTax * $quantity;

            if (!empty($value['reference'])) {
                $sku = $value['reference'];
            }

            if (!empty($value['ean13'])) {
                $sku = $value['ean13'];
            }

            if (!empty($value['isbn'])) {
                $sku = $value['isbn'];
            }

            if (!empty($value['upc'])) {
                $sku = $value['upc'];
            }

            $paypalItem = [];
            $paypalItem['name'] = StringUtility::truncate($value['name'], 127);
            $paypalItem['description'] = !empty($value['attributes']) ? StringUtility::truncate($value['attributes'], 127) : '';
            $paypalItem['sku'] = StringUtility::truncate($sku, 127);
            $paypalItem['unit_amount']['currency_code'] = $currencyIsoCode;
            $paypalItem['unit_amount']['value'] = $unitPriceWithoutTax;
            $paypalItem['tax']['currency_code'] = $currencyIsoCode;
            $paypalItem['tax']['value'] = $unitTax;
            $paypalItem['quantity'] = $quantity;
            $paypalItem['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $node['items'][] = $paypalItem;
        }

        $node['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => $currencyIsoCode,
                'value' => NumberUtility::formatAmount($breakdownItemTotal, $currencyIsoCode),
            ],
            'shipping' => [
                'currency_code' => $currencyIsoCode,
                'value' => NumberUtility::formatAmount($breakdownShipping, $currencyIsoCode),
            ],
            'tax_total' => [
                'currency_code' => $currencyIsoCode,
                'value' => NumberUtility::formatAmount($breakdownTaxTotal, $currencyIsoCode),
            ],
        ];

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (!empty($this->cart['cart']['subtotals']['gift_wrapping']['amount'])) {
            $breakdownHandling += $this->cart['cart']['subtotals']['gift_wrapping']['amount'];
        }

        $remainderValue = $amountTotal - $breakdownItemTotal - $breakdownTaxTotal - $breakdownShipping - $breakdownHandling;

        // In case of rounding issue, if remainder value is negative we use discount value to deduct remainder and if remainder value is positive we use handling value to add remainder
        if ($remainderValue < 0) {
            $breakdownDiscount += abs($remainderValue);
        } else {
            $breakdownHandling += $remainderValue;
        }

        $node['amount']['breakdown']['discount'] = [
            'currency_code' => $currencyIsoCode,
            'value' => NumberUtility::formatAmount($breakdownDiscount, $currencyIsoCode),
        ];

        $node['amount']['breakdown']['handling'] = [
            'currency_code' => $currencyIsoCode,
            'value' => NumberUtility::formatAmount($breakdownHandling, $currencyIsoCode),
        ];

        return $node;
    }

    /**
     * {@inheritDoc}
     */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }
}
