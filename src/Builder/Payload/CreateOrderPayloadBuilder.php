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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

/**
 * Build the payload for Create PayPal Order
 */
class CreateOrderPayloadBuilder extends Builder
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        parent::__construct();
        $this->data = $data;
    }

    /**
     * Build payload with cart details
     */
    public function buildFullPayload()
    {
        parent::buildFullPayload();

        $this->buildBaseNode();
        $this->buildAmountBreakdownNode();

        if (empty($this->data['ps_checkout']['isExpressCheckout'])) {
            $this->buildShippingNode();

            if (empty($this->data['ps_checkout']['isUpdate'])) {
                $this->buildPayerNode();
            }
        }

        if (empty($this->data['ps_checkout']['isUpdate'])) {
            $this->buildApplicationContextNode();
        }
    }

    /**
     * Build payload without cart details
     */
    public function buildMinimalPayload()
    {
        parent::buildMinimalPayload();

        $this->buildBaseNode();

        if (empty($this->data['ps_checkout']['isExpressCheckout'])) {
            $this->buildShippingNode();

            if (empty($this->data['ps_checkout']['isUpdate'])) {
                $this->buildPayerNode();
            }
        }

        if (empty($this->data['ps_checkout']['isUpdate'])) {
            $this->buildApplicationContextNode();
        }
    }

    /**
     * Build the basic payload
     */
    public function buildBaseNode()
    {
        $node = [
            'intent' => $this->data['ps_checkout']['intent'],
            'custom_id' => (string) $this->data['cart']['id'],
            'invoice_id' => '',
            'description' => $this->truncate(
                'Checking out with your cart ' . $this->data['cart']['id'] . ' from ' . $this->data['shop']['name'],
                127
            ),
            'amount' => [
                'currency_code' => $this->data['currency']['iso_code'],
                'value' => $this->formatAmount($this->data['totalWithTaxes']),
            ],
            'payee' => [
                'merchant_id' => $this->data['ps_checkout']['merchant_id'],
            ],
        ];

        if (empty($this->data['ps_checkout']['isUpdate']) && !empty($this->data['ps_checkout']['token'])) {
            $node['token'] = $this->data['ps_checkout']['token'];
        }

        if (empty($this->data['ps_checkout']['isUpdate'])) {
            $node['roundingConfig'] = $this->data['ps_checkout']['roundType'] . '-' . $this->data['ps_checkout']['roundMode'];
        }

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build shipping node
     */
    public function buildShippingNode()
    {
        $node['shipping'] = [
            'name' => [
                'full_name' => trim(
                    (!empty($this->data['deliveryAddress']['firstname']) ? $this->data['deliveryAddress']['firstname'] : '')
                    . ' '
                    . (!empty($this->data['deliveryAddress']['lastname']) ? $this->data['deliveryAddress']['lastname'] : '')
                ),
            ],
            'address' => [
                'address_line_1' => !empty($this->data['deliveryAddress']['address1']) ? $this->data['deliveryAddress']['address1'] : '',
                'address_line_2' => !empty($this->data['deliveryAddress']['address2']) ? $this->data['deliveryAddress']['address2'] : '',
                'admin_area_1' => !empty($this->data['deliveryAddressState']['name']) ? $this->data['deliveryAddressState']['name'] : '',
                'admin_area_2' => !empty($this->data['deliveryAddress']['city']) ? $this->data['deliveryAddress']['city'] : '',
                'country_code' => !empty($this->data['deliveryAddressCountry']['iso_code']) ? $this->data['deliveryAddressCountry']['iso_code'] : '',
                'postal_code' => !empty($this->data['deliveryAddress']['postcode']) ? $this->data['deliveryAddress']['postcode'] : '',
            ],
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build payer node
     */
    public function buildPayerNode()
    {
        $node['payer'] = [
            'name' => [
                'given_name' => !empty($this->data['invoiceAddress']['firstname']) ? $this->data['invoiceAddress']['firstname'] : '',
                'surname' => !empty($this->data['invoiceAddress']['lastname']) ? $this->data['invoiceAddress']['lastname'] : '',
            ],
            'email_address' => !empty($this->data['customer']['email']) ? $this->data['customer']['email'] : '',
            'address' => [
                'address_line_1' => !empty($this->data['invoiceAddress']['address1']) ? $this->data['deliveryAddress']['address1'] : '',
                'address_line_2' => !empty($this->data['invoiceAddress']['address2']) ? $this->data['deliveryAddress']['address2'] : '',
                'admin_area_1' => !empty($this->data['invoiceAddressState']['name']) ? $this->data['deliveryAddressState']['name'] : '',
                'admin_area_2' => !empty($this->data['invoiceAddress']['city']) ? $this->data['deliveryAddress']['city'] : '',
                'country_code' => !empty($this->data['invoiceAddressCountry']['iso_code']) ? $this->data['deliveryAddressCountry']['iso_code'] : '',
                'postal_code' => !empty($this->data['invoiceAddress']['postcode']) ? $this->data['deliveryAddress']['postcode'] : '',
            ],
        ];

        // Add optional birthdate if provided
        if (!empty($this->data['customer']['birthday']) && $this->data['customer']['birthday'] !== '0000-00-00') {
            $node['payer']['birth_date'] = $this->data['customer']['birthday'];
        }

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build application context node
     *
     * NO_SHIPPING: The client can customize his address int the paypal pop-up (used in express checkout mode)
     * SET_PROVIDED_ADDRESS: The address is provided by prestashop and the client
     * cannot change/edit his address in the paypal pop-up
     */
    public function buildApplicationContextNode()
    {
        $node['application_context'] = [
            'brand_name' => $this->data['shop']['name'],
            'shipping_preference' => empty($this->data['ps_checkout']['isExpressCheckout']) ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE',
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build the amount breakdown node
     */
    public function buildAmountBreakdownNode()
    {
        $node = [];
        $amountTotal = $this->data['totalWithTaxes'];
        $breakdownItemTotal = 0;
        $breakdownTaxTotal = 0;
        $breakdownShipping = $this->data['totalShippingWithTaxes'];
        $breakdownHandling = 0;
        $breakdownDiscount = 0;

        foreach ($this->data['products'] as $product) {
            $sku = '';
            $totalWithoutTax = $product['total'];
            $totalWithTax = $product['total_wt'];
            $totalTax = $totalWithTax - $totalWithoutTax;
            $quantity = $product['quantity'];
            $unitPriceWithoutTax = $this->formatAmount($totalWithoutTax / $quantity);
            $unitTax = $this->formatAmount($totalTax / $quantity);
            $breakdownItemTotal += $unitPriceWithoutTax * $quantity;
            $breakdownTaxTotal += $unitTax * $quantity;

            if (false === empty($product['reference'])) {
                $sku = $product['reference'];
            }

            if (false === empty($product['ean13'])) {
                $sku = $product['ean13'];
            }

            if (false === empty($product['isbn'])) {
                $sku = $product['isbn'];
            }

            if (false === empty($product['upc'])) {
                $sku = $product['upc'];
            }

            $paypalItem = [];
            $paypalItem['name'] = $this->truncate($product['name'], 127);
            $paypalItem['description'] = false === empty($product['attributes']) ? $this->truncate($product['attributes'], 127) : '';
            $paypalItem['sku'] = $this->truncate($sku, 127);
            $paypalItem['unit_amount']['currency_code'] = $this->data['currency']['iso_code'];
            $paypalItem['unit_amount']['value'] = $unitPriceWithoutTax;
            $paypalItem['tax']['currency_code'] = $this->data['currency']['iso_code'];
            $paypalItem['tax']['value'] = $unitTax;
            $paypalItem['quantity'] = $quantity;
            $paypalItem['category'] = $product['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $node['items'][] = $paypalItem;
        }

        $node['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => $this->data['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownItemTotal),
            ],
            'shipping' => [
                'currency_code' => $this->data['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownShipping),
            ],
            'tax_total' => [
                'currency_code' => $this->data['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownTaxTotal),
            ],
        ];

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (!empty($this->data['totalGiftWrappingWithTaxes'])) {
            $breakdownHandling += $this->data['totalGiftWrappingWithTaxes'];
        }

        $remainderValue = $amountTotal - $breakdownItemTotal - $breakdownTaxTotal - $breakdownShipping - $breakdownHandling;

        // In case of rounding issue, if remainder value is negative we use discount value to deduct remainder and if remainder value is positive we use handling value to add remainder
        if ($remainderValue < 0) {
            $breakdownDiscount += abs($remainderValue);
        } else {
            $breakdownHandling += $remainderValue;
        }

        $node['amount']['breakdown']['discount'] = [
            'currency_code' => $this->data['currency']['iso_code'],
            'value' => $this->formatAmount($breakdownDiscount),
        ];

        $node['amount']['breakdown']['handling'] = [
            'currency_code' => $this->data['currency']['iso_code'],
            'value' => $this->formatAmount($breakdownHandling),
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Get decimal to round correspondent to the payment currency used
     * Advise from PayPal: Always round to 2 decimals except for HUF, JPY and TWD
     * currencies which require a round with 0 decimal
     *
     * @return int
     */
    private function getNbDecimalToRound()
    {
        if (in_array($this->data['currency']['iso_code'], ['HUF', 'JPY', 'TWD'], true)) {
            return 0;
        }

        return 2;
    }

    /**
     * @param float|int|string $amount
     *
     * @return string
     */
    private function formatAmount($amount)
    {
        return sprintf("%01.{$this->getNbDecimalToRound()}f", $amount);
    }

    /**
     * Function that allow to truncate fields to match the
     * paypal api requirements
     *
     * @param string $str
     * @param int $limit
     *
     * @return string
     */
    private function truncate($str, $limit)
    {
        return mb_substr($str, 0, $limit);
    }
}
