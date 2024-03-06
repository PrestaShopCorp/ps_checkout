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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\Cart\CartInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CreatePayPalOrderPayloadBuilderInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\AddressRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\Amount;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\AmountBreakdown;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\AmountWithBreakdown;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\ApplicationContextRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\ItemRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\Name;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PayeeRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\Payer;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\PurchaseUnitRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\ShippingRequest;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\StoredPaymentSourceRequest;

class CreatePayPalOrderPayloadBuilder implements CreatePayPalOrderPayloadBuilderInterface
{
    /** @var CreatePayPalOrderRequest */
    private $payload;
    /**
     * @var array
     */
    private $data;

    /**
     * {@inheritDoc}
     */
    public function build(CartInterface $cart, $fundingSource, $data)
    {
        $this->data = $data;
        $this->buildBaseNode();
        $this->buildPurchaseUnitsNode();
        if (empty($this->data['ps_checkout']['isExpressCheckout']) && empty($this->data['ps_checkout']['isUpdate'])) {
            $this->buildPayerNode();
        }

        if (empty($this->data['ps_checkout']['isUpdate'])) {
            $this->buildApplicationContextNode();
        }

        return $this->payload;
    }

    /**
     * Build payload without cart details
     */
    public function buildMinimalPayload()
    {
        $this->buildBaseNode();
        $this->buildPurchaseUnitsNode();

        if (empty($this->data['ps_checkout']['isExpressCheckout']) && empty($this->data['ps_checkout']['isUpdate'])) {
            $this->buildPayerNode();
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
        $this->payload = (new CreatePayPalOrderRequest())->setIntent($this->data['ps_checkout']['intent']);
//        if (empty($this->data['ps_checkout']['isUpdate']) && !empty($this->data['ps_checkout']['token'])) {
//            $node['token'] = $this->data['ps_checkout']['token'];
//        }
//
//        if (empty($this->data['ps_checkout']['isUpdate'])) {
//            $node['roundingConfig'] = $this->data['ps_checkout']['roundType'] . '-' . $this->data['ps_checkout']['roundMode'];
//        }
    }

    /**
     * Build shipping node
     */
    public function buildShippingNode(PurchaseUnitRequest $purchaseUnit)
    {
        $shipping = new ShippingRequest();
        $shipping->setName(
            (new Name())->setFullName(trim(
                (!empty($this->data['deliveryAddress']['firstname']) ? $this->data['deliveryAddress']['firstname'] : '')
                . ' '
                . (!empty($this->data['deliveryAddress']['lastname']) ? $this->data['deliveryAddress']['lastname'] : '')
            ))
        )->setAddress($this->buildAddress($this->data['deliveryAddress'], $this->data['deliveryAddressCountry'], $this->data['deliveryAddressState']));
        $purchaseUnit->setShipping($shipping);
    }

    /**
     * Build payer node
     */
    public function buildPayerNode()
    {
        $payer = new Payer();
        $payer->setName(
            (new Name())
                ->setGivenName(!empty($this->data['invoiceAddress']['firstname']) ? $this->data['invoiceAddress']['firstname'] : '')
                ->setSurname(!empty($this->data['invoiceAddress']['lastname']) ? $this->data['invoiceAddress']['lastname'] : '')
            )
            ->setEmailAddress(!empty($this->data['customer']['email']) ? $this->data['customer']['email'] : '')
            ->setAddress($this->buildAddress($this->data['invoiceAddress'], $this->data['invoiceAddressCountry'], $this->data['invoiceAddressState']));

        // Add optional birthdate if provided
        if (!empty($this->data['customer']['birthday']) && $this->data['customer']['birthday'] !== '0000-00-00') {
            $payer->setBirthDate($this->data['customer']['birthday']);
        }

        $this->payload->setPayer($payer);
    }

    /**
     * @param array $address
     *
     * @return AddressRequest
     */
    private function buildAddress($address, $country, $state)
    {
        return (new AddressRequest())
            ->setAddressLine1(!empty($address['address1']) ? $address['address1'] : '')
            ->setAddressLine2(!empty($address['address2']) ? $address['address2'] : '')
            ->setAdminArea1(!empty($state['name']) ? $state['name'] : '')
            ->setAdminArea2(!empty($address['city']) ? $address['city'] : '')
            ->setCountryCode(!empty($country['iso_code']) ? $country['iso_code'] : '')
            ->setPostalCode(!empty($address['postcode']) ? $address['postcode'] : '');
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
        $applicationContext = (new ApplicationContextRequest())
            ->setStoredPaymentSource(
                (new StoredPaymentSourceRequest())
                    ->setPaymentType('ONE_TIME')
                    ->setPaymentInitiator('MERCHANT')
            );
        // DEPRECATED
//        $node['application_context'] = [
//            'brand_name' => $this->data['shop']['name'],
//            'shipping_preference' => empty($this->data['ps_checkout']['isExpressCheckout']) ? 'SET_PROVIDED_ADDRESS' : 'GET_FROM_FILE',
//        ];
        $this->payload->setApplicationContext($applicationContext);
    }

    public function buildPurchaseUnitsNode()
    {
        $purchaseUnit = (new PurchaseUnitRequest())
            ->setPayee((new PayeeRequest())->setMerchantId($this->data['ps_checkout']['merchant_id']))
            ->setDescription($this->truncate(
                'Checking out with your cart ' . $this->data['cart']['id'] . ' from ' . $this->data['shop']['name'],
                127
            ))
            ->setCustomId((string) $this->data['cart']['id'])
            ->setInvoiceId('');
        $this->buildAmountBreakdownNode($purchaseUnit);

        if ($this->payload->getIntent() === 'CAPTURE') {
//            $purchaseUnit->setPaymentInstruction(); // TODO: Need to add?
        }

        if (empty($this->data['ps_checkout']['isExpressCheckout'])) {
            $this->buildShippingNode($purchaseUnit);
        }

        $this->payload->setPurchaseUnits([$purchaseUnit]);
    }

    /**
     * Build the amount breakdown node
     */
    public function buildAmountBreakdownNode(PurchaseUnitRequest $purchaseUnit)
    {
        $items = [];
        $amountTotal = $this->data['totalWithTaxes'];
        $breakdownItemTotal = 0;
        $breakdownTaxTotal = 0;
        $breakdownShipping = $this->data['totalShippingWithTaxes'];
        $breakdownHandling = 0;
        $breakdownDiscount = 0;
        $currencyCode = $this->data['currency']['iso_code'];

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

            if (!empty($product['reference'])) {
                $sku = $product['reference'];
            }

            if (!empty($product['ean13'])) {
                $sku = $product['ean13'];
            }

            if (!empty($product['isbn'])) {
                $sku = $product['isbn'];
            }

            if (!empty($product['upc'])) {
                $sku = $product['upc'];
            }

            $paypalItem = (new ItemRequest())
                ->setName($this->truncate($product['name'], 127))
                ->setDescription(!empty($product['attributes']) ? $this->truncate($product['attributes'], 127) : '')
                ->setSku($this->truncate($sku, 127))
                ->setUnitAmount((new Amount())->setValue($unitPriceWithoutTax)->setCurrencyCode($currencyCode))
                ->setTax((new Amount())->setValue($unitTax)->setCurrencyCode($currencyCode))
                ->setQuantity($quantity)
                ->setCategory($product['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS');

            $items[] = $paypalItem;
        }

        $purchaseUnit->setItems($items);

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

        $amountBreakdown = (new AmountBreakdown())
            ->setItemTotal((new Amount())->setValue($this->formatAmount($breakdownItemTotal))->setCurrencyCode($currencyCode))
            ->setShipping((new Amount())->setValue($this->formatAmount($breakdownShipping))->setCurrencyCode($currencyCode))
            ->setTaxTotal((new Amount())->setValue($this->formatAmount($breakdownTaxTotal))->setCurrencyCode($currencyCode))
            ->setDiscount((new Amount())->setValue($this->formatAmount($breakdownDiscount))->setCurrencyCode($currencyCode))
            ->setHandling((new Amount())->setValue($this->formatAmount($breakdownHandling))->setCurrencyCode($currencyCode));

        $purchaseUnit->setAmount(
            (new AmountWithBreakdown())
                ->setBreakdown($amountBreakdown)
                ->setValue($this->formatAmount($this->data['totalWithTaxes']))
                ->setCurrencyCode($currencyCode)
        );
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
