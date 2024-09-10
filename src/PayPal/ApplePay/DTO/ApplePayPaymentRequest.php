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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\DTO;

class ApplePayPaymentRequest
{
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $currencyCode;
    /**
     * @var ApplePayLineItem|null
     */
    private $total = null;
    /**
     * @var ApplePayLineItem[]
     */
    private $lineItems = [];

    /**
     * @var ApplePayPaymentContact|null
     */
    private $shippingContact = null;
    /**
     * @var ApplePayPaymentContact|null
     */
    private $billingContact = null;

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     *
     * @return ApplePayPaymentRequest
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     *
     * @return ApplePayPaymentRequest
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return ApplePayLineItem
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param ApplePayLineItem $total
     *
     * @return ApplePayPaymentRequest
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * @return ApplePayLineItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @param ApplePayLineItem[] $lineItems
     *
     * @return ApplePayPaymentRequest
     */
    public function setLineItems($lineItems)
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    /**
     * @return ApplePayPaymentContact
     */
    public function getShippingContact()
    {
        return $this->shippingContact;
    }

    /**
     * @param ApplePayPaymentContact $shippingContact
     *
     * @return ApplePayPaymentRequest
     */
    public function setShippingContact($shippingContact)
    {
        $this->shippingContact = $shippingContact;

        return $this;
    }

    /**
     * @return ApplePayPaymentContact
     */
    public function getBillingContact()
    {
        return $this->billingContact;
    }

    /**
     * @param ApplePayPaymentContact $billingContact
     *
     * @return ApplePayPaymentRequest
     */
    public function setBillingContact($billingContact)
    {
        $this->billingContact = $billingContact;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'countryCode' => $this->countryCode,
            'currencyCode' => $this->currencyCode,
            'total' => $this->total ? $this->total->toArray() : null,
            'lineItems' => array_map(function (ApplePayLineItem $lineItem) {
                return $lineItem->toArray();
            }, $this->lineItems),
            'shippingContact' => $this->shippingContact ? $this->shippingContact->toArray() : null,
            'billingContact' => $this->billingContact ? $this->billingContact->toArray() : null,
        ]);
    }
}
