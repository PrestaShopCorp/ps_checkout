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

namespace PsCheckout\Core\PayPal\GooglePay\ValueObject;

/**
 * Class GooglePayPaymentRequestData
 * Represents a Google Pay payment request data object.
 */
class GooglePayPaymentRequestData
{
    const TOTAL_PRICE_STATUS_FINAL = 'FINAL';

    const CHECKOUT_OPTION_DEFAULT = 'DEFAULT';

    /** @var string */
    private $currencyCode;

    /** @var string */
    private $totalPriceStatus;

    /** @var string */
    private $totalPrice;

    /** @var string */
    private $totalPriceLabel;

    /**
     * @var string
     */
    private $merchantName;

    /** @var string */
    private $checkoutOption;

    /**
     * GooglePayPaymentRequestData constructor.
     *
     * @param string $currencyCode
     * @param string $totalPrice
     * @param string $totalPriceLabel
     * @param string $merchantName
     * @param string $totalPriceStatus
     * @param string $checkoutOption
     */
    public function __construct(
        string $currencyCode,
        string $totalPrice,
        string $totalPriceLabel,
        string $merchantName,
        string $totalPriceStatus = self::TOTAL_PRICE_STATUS_FINAL,
        string $checkoutOption = self::CHECKOUT_OPTION_DEFAULT
    ) {
        $this->currencyCode = $currencyCode;
        $this->totalPrice = $totalPrice;
        $this->totalPriceLabel = $totalPriceLabel;
        $this->merchantName = $merchantName;
        $this->totalPriceStatus = $totalPriceStatus;
        $this->checkoutOption = $checkoutOption;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getTotalPriceStatus()
    {
        return $this->totalPriceStatus;
    }

    /**
     * @return string
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * @return string
     */
    public function getTotalPriceLabel()
    {
        return $this->totalPriceLabel;
    }

    /**
     * @return string
     */
    public function getCheckoutOption()
    {
        return $this->checkoutOption;
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * Convert object data to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'currencyCode' => $this->currencyCode,
            'totalPriceStatus' => $this->totalPriceStatus,
            'totalPrice' => $this->totalPrice,
            'totalPriceLabel' => $this->totalPriceLabel,
            'checkoutOption' => $this->checkoutOption,
            'merchantInfo' => [
                'merchantName' => $this->merchantName,
            ],
        ];
    }
}
