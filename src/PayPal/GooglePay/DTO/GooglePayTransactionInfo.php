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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO;

class GooglePayTransactionInfo
{
    const TOTAL_PRICE_STATUS_ESTIMATED = 'ESTIMATED';
    const TOTAL_PRICE_STATUS_FINAL = 'FINAL';
    const CHECKOUT_OPTION_DEFAULT = 'DEFAULT';
    const CHECKOUT_OPTION_COMPLETE_IMMEDIATE_PURCHASE = 'COMPLETE_IMMEDIATE_PURCHASE';

    /**
     * @var string
     */
    private $currencyCode;
    /**
     * @var string
     */
    private $countryCode = null;
    /**
     * @var string
     */
    private $transactionId = null;
    /**
     * @var 'ESTIMATED'|'FINAL'
     */
    private $totalPriceStatus = self::TOTAL_PRICE_STATUS_FINAL;
    /**
     * @var string
     */
    private $totalPrice;
    /**
     * @var GooglePayDisplayItem[]
     */
    private $displayItems = [];
    /**
     * @var string
     */
    private $totalPriceLabel;
    /**
     * @var 'DEFAULT'|'COMPLETE_IMMEDIATE_PURCHASE'
     */
    private $checkoutOption = self::CHECKOUT_OPTION_DEFAULT;
    /**
     * @var MerchantInfo
     */
    private $merchantInfo;

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
     * @return GooglePayTransactionInfo
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
     * @return GooglePayTransactionInfo
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return GooglePayTransactionInfo
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTotalPriceStatus()
    {
        return $this->totalPriceStatus;
    }

    /**
     * @param 'ESTIMATED'|'FINAL' $totalPriceStatus
     *
     * @return GooglePayTransactionInfo
     */
    public function setTotalPriceStatus($totalPriceStatus)
    {
        $this->totalPriceStatus = $totalPriceStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @param string $totalPrice
     *
     * @return GooglePayTransactionInfo
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * @return GooglePayDisplayItem[]
     */
    public function getDisplayItems()
    {
        return $this->displayItems;
    }

    /**
     * @param GooglePayDisplayItem[] $displayItems
     *
     * @return GooglePayTransactionInfo
     */
    public function setDisplayItems($displayItems)
    {
        $this->displayItems = $displayItems;

        return $this;
    }

    /**
     * @return string
     */
    public function getTotalPriceLabel()
    {
        return $this->totalPriceLabel;
    }

    /**
     * @param string $totalPriceLabel
     *
     * @return GooglePayTransactionInfo
     */
    public function setTotalPriceLabel($totalPriceLabel)
    {
        $this->totalPriceLabel = $totalPriceLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckoutOption()
    {
        return $this->checkoutOption;
    }

    /**
     * @param 'DEFAULT'|'COMPLETE_IMMEDIATE_PURCHASE' $checkoutOption
     *
     * @return GooglePayTransactionInfo
     */
    public function setCheckoutOption($checkoutOption)
    {
        $this->checkoutOption = $checkoutOption;

        return $this;
    }

    /**
     * @param MerchantInfo $merchantInfo
     *
     * @return GooglePayTransactionInfo
     */
    public function setMerchantInfo($merchantInfo)
    {
        $this->merchantInfo = $merchantInfo;

        return $this;
    }

    /**
     * @return MerchantInfo
     */
    public function getMerchantInfo()
    {
        return $this->merchantInfo;
    }

    public function toArray()
    {
        return array_filter([
            'currencyCode' => $this->currencyCode,
            'countryCode' => $this->countryCode,
            'transactionId' => $this->transactionId,
            'totalPriceStatus' => $this->totalPriceStatus,
            'totalPrice' => $this->totalPrice,
            'totalPriceLabel' => $this->totalPriceLabel,
            'checkoutOption' => $this->checkoutOption,
            'displayItems' => array_map(function (GooglePayDisplayItem $item) {
                return $item->toArray();
            }, $this->displayItems),
            'merchantInfo' => $this->merchantInfo->toArray(),
        ]);
    }
}
