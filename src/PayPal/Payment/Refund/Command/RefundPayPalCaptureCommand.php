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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Command;

use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Exception\PayPalRefundException;
use Validate;

class RefundPayPalCaptureCommand
{
    /**
     * @var string
     */
    private $orderPayPalId;
    /**
     * @var string
     */
    private $captureId;
    /**
     * @var string
     */
    private $currencyCode;
    /**
     * @var string
     */
    private $amount;

    /**
     * @param string $orderPayPalId
     * @param string $captureId
     * @param string $currencyCode
     * @param mixed $amount
     *
     * @throws PayPalRefundException
     */
    public function __construct($orderPayPalId, $captureId, $currencyCode, $amount)
    {
        if (empty($orderPayPalId) || !Validate::isGenericName($orderPayPalId)) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_ORDER_ID);
        }

        if (empty($captureId) || !Validate::isGenericName($captureId)) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_TRANSACTION_ID);
        }

        // https://developer.paypal.com0/docs/api/reference/currency-codes/
        if (empty($currencyCode) || !in_array($currencyCode, ['AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'INR', 'ILS', 'JPY', 'MYR', 'MXN', 'TWD', 'NZD', 'NOK', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'THB', 'USD'])) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_CURRENCY);
        }

        if (empty($amount) || !Validate::isPrice($amount) || $amount <= 0) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_AMOUNT);
        }

        $this->orderPayPalId = $orderPayPalId;
        $this->captureId = $captureId;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getOrderPayPalId()
    {
        return $this->orderPayPalId;
    }

    /**
     * @return string
     */
    public function getCaptureId()
    {
        return $this->captureId;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
