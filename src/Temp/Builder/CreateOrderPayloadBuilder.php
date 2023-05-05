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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Builder;

use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Address;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Amount;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\AmountBreakdown;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\ApplicationContext;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Item;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Money;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Payee;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Payer;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\PayerName;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Phone;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\PhoneWithType;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\PurchaseUnit;
use PrestaShop\Module\PrestashopCheckout\Temp\Entities\Shipping;
use PrestaShop\Module\PrestashopCheckout\Temp\Provider\OrderDataProvider;

class CreateOrderPayloadBuilder
{
    /** @var OrderDataProvider */
    private $orderDataProvider;

    /**
     * @param OrderDataProvider $orderDataProvider
     */
    public function __construct($orderDataProvider)
    {
        $this->orderDataProvider = $orderDataProvider;
    }

    /**
     * @param bool
     *
     * @return array
     */
    public function buildPayload($toArray)
    {
        return [
            'application_context' => $this->buildApplicationContextNode($toArray),
            'intent' => 'CAPTURE',
            'payer' => $this->buildPayerNode($toArray),
            'purchase_units' => $this->buildPurchaseUnitsNode($toArray),
        ];
    }

    /**
     * @param bool
     *
     * @return array|ApplicationContext
     */
    private function buildApplicationContextNode($toArray)
    {
        $applicationContext = new ApplicationContext();
        $applicationContext->setBrandName($this->orderDataProvider->getBrandName());
        $applicationContext->setShippingPreference($this->orderDataProvider->getShippingPreference());

        return $toArray ? $applicationContext->toArray() : $applicationContext;
    }

    /**
     * @param bool
     *
     * @return array|Payer
     */
    private function buildPayerNode($toArray)
    {
        $payerName = new PayerName(
            $this->orderDataProvider->getPayerGivenName(),
            $this->orderDataProvider->getPayerSurname()
        );

        $address = new Address($this->orderDataProvider->getPayerCountryCode());
        $address->setAddressLine1($this->orderDataProvider->getPayerAddressLine1());
        $address->setAddressLine2($this->orderDataProvider->getPayerAddressLine2());
        $address->setAdminArea1($this->orderDataProvider->getPayerAdminArea1());
        $address->setAdminArea2($this->orderDataProvider->getPayerAdminArea2());
        $address->setPostalCode($this->orderDataProvider->getPayerPostalCode());

        $phone = new PhoneWithType(new Phone($this->orderDataProvider->getPayerPhone()));
        $phone->setPhoneType($this->orderDataProvider->getPayerPhoneType());

        $payer = new Payer($this->orderDataProvider->getPayerEmailAddress(), $this->orderDataProvider->getPayerId());
        $payer->setName($payerName);
        $payer->setAddress($address);
        $payer->setBirthDate($this->orderDataProvider->getPayerBirthdate());
        $payer->setPhone($phone);
        $payer->setTaxInfo(null);

        return $toArray ? $payer->toArray() : $payer;
    }

    /**
     * @param bool
     *
     * @return array
     */
    private function buildPurchaseUnitsNode($toArray)
    {
        $payee = new Payee(
            $this->orderDataProvider->getPayeeEmailAddress(),
            $this->orderDataProvider->getPayeeMerchantId()
        );

        $address = new Address($this->orderDataProvider->getShippingCountryCode());
        $address->setAddressLine1($this->orderDataProvider->getShippingAddressLine1());
        $address->setAddressLine2($this->orderDataProvider->getShippingAddressLine2());
        $address->setAdminArea1($this->orderDataProvider->getShippingAdminArea1());
        $address->setAdminArea2($this->orderDataProvider->getShippingAdminArea2());
        $address->setPostalCode($this->orderDataProvider->getShippingPostalCode());

        $shipping = new Shipping(
            $address,
            $this->orderDataProvider->getShippingFullName(),
            $this->orderDataProvider->getShippingType()
        );

        $data = $this->createItemsAndAmount();

        $purchaseUnit = new PurchaseUnit($data['amount']);
        $purchaseUnit->setCustomId($this->orderDataProvider->getPurchaseUnitCustomId());
        $purchaseUnit->setDescription($this->orderDataProvider->getPurchaseUnitDescription());
        $purchaseUnit->setInvoiceId($this->orderDataProvider->getPurchaseUnitInvoiceId());
        $purchaseUnit->setItems($data['items']);
        $purchaseUnit->setPayee($payee);
        $purchaseUnit->setReferenceId($this->orderDataProvider->getPurchaseUnitReferenceId());
        $purchaseUnit->setShipping($shipping);
        $purchaseUnit->setSoftDescriptor($this->orderDataProvider->getPurchaseUnitSoftDescriptor());

        return $toArray ? [$purchaseUnit->toArray()] : [$purchaseUnit];
    }

    /**
     * @return array
     */
    private function createItemsAndAmount()
    {
        $data = [];

        $breakdownItemTotal = $breakdownTaxTotal = $breakdownHandling = $breakdownDiscount = 0;
        $breakdownInsurance = $breakdownShippingDiscount = 0;
        $breakdownShipping = $this->orderDataProvider->getShippingCost();
        $amountTotal = $this->orderDataProvider->getCartTotalAmount();
        $currencyCode = $this->orderDataProvider->getCurrencyCode();

        $cartItems = $this->orderDataProvider->getCartItems();
        foreach ($cartItems as $cartItem) {
            $totalWithoutTax = $cartItem['total'];
            $totalWithTax = $cartItem['total_wt'];
            $totalTax = $totalWithTax - $totalWithoutTax;
            $quantity = $cartItem['quantity'];
            $unitPriceWithoutTax = $this->formatAmount($totalWithoutTax / $quantity);
            $unitTax = $this->formatAmount($totalTax / $quantity);
            $breakdownItemTotal += $unitPriceWithoutTax * $quantity;
            $breakdownTaxTotal += $unitTax * $quantity;

            $sku = '';
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

            $category = $cartItem['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';
            $description = !empty($cartItem['attributes']) ? $this->truncate($cartItem['attributes']) : '';
            $unitAmount = new Money($currencyCode, $unitPriceWithoutTax);
            $item = new Item($this->truncate($cartItem['name']), $quantity, $unitAmount);
            $item->setCategory($category);
            $item->setDescription($description);
            $item->setSku($sku);
            $item->setTax(new Money($this->orderDataProvider->getCurrencyCode(), $unitTax));

            $data['items'][] = $item;
        }

        $breakdownHandling += $this->orderDataProvider->getGiftWrappingAmount();

        $remainderValue = $amountTotal - $breakdownItemTotal - $breakdownTaxTotal - $breakdownShipping - $breakdownHandling;
        // In case of rounding issue, if remainder value is negative we use discount value to deduct remainder and if remainder value is positive we use handling value to add remainder
        if ($remainderValue < 0) {
            $breakdownDiscount += abs($remainderValue);
        } else {
            $breakdownHandling += $remainderValue;
        }

        $breakdown = new AmountBreakdown();
        $breakdown->setDiscount(new Money($currencyCode, $breakdownDiscount));
        $breakdown->setHandling(new Money($currencyCode, $breakdownHandling));
        $breakdown->setInsurance(new Money($currencyCode, $breakdownInsurance));
        $breakdown->setItemTotal(new Money($currencyCode, $breakdownItemTotal));
        $breakdown->setShipping(new Money($currencyCode, $breakdownShipping));
        $breakdown->setShippingDiscount(new Money($currencyCode, $breakdownShippingDiscount));
        $breakdown->setTaxTotal(new Money($currencyCode, $breakdownTaxTotal));

        $data['amount'] = new Amount(new Money($this->orderDataProvider->getCurrencyCode(), $amountTotal), $breakdown);

        return $data;
    }

    private function truncate($str, $limit = 127)
    {
        return mb_substr($str, 0, $limit);
    }

    private function formatAmount($amount)
    {
        return sprintf("%01.{$this->getNbDecimalToRound()}f", $amount);
    }

    private function getNbDecimalToRound()
    {
        if (in_array($this->orderDataProvider->getCurrencyCode(), ['HUF', 'JPY', 'TWD'], true)) {
            return 0;
        }

        return 2;
    }
}
