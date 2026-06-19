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

namespace PsCheckout\Core\PayPal\ShippingCallback\Builder;

class PurchaseUnitsNodeBuilder implements PurchaseUnitsNodeBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function build(
        string $referenceId,
        string $currencyCode,
        float $itemTotal,
        float $taxTotal,
        float $selectedShippingPrice,
        array $shippingOptions
    ): array {
        // $selectedShippingPrice is price_with_tax (gross). Shipping tax is embedded in the
        // 'shipping' breakdown field rather than split into 'tax_total'. The breakdown sum
        // still equals 'value': productsNet + productTax + shippingGross = productsGross + shippingGross.
        $total = round($itemTotal + $taxTotal + $selectedShippingPrice, 2);

        return [
            'purchase_units' => [
                [
                    'reference_id' => $referenceId,
                    'amount' => [
                        'currency_code' => $currencyCode,
                        'value' => number_format($total, 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($itemTotal, 2, '.', ''),
                            ],
                            'tax_total' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($taxTotal, 2, '.', ''),
                            ],
                            'shipping' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($selectedShippingPrice, 2, '.', ''),
                            ],
                        ],
                    ],
                    'shipping_options' => $shippingOptions,
                ],
            ],
        ];
    }
}
