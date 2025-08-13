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

namespace PsCheckout\Core\PayPal\Refund\ValueObject;

use PsCheckout\Core\PayPal\Refund\Exception\PayPalRefundException;
use PsCheckout\Core\Settings\Configuration\PayPalCodeConfiguration;
use Validate;

class PayPalRefund
{
    /**
     * @var string
     */
    private $payPalOrderId;

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
     * @param string $payPalOrderId
     * @param string $captureId
     * @param string $currencyCode
     * @param string $amount
     *
     * @throws PayPalRefundException
     */
    public function __construct(string $payPalOrderId, string $captureId, string $currencyCode, string $amount)
    {
        if (empty($payPalOrderId) || !Validate::isGenericName($payPalOrderId)) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_ORDER_ID);
        }

        if (empty($captureId) || !Validate::isGenericName($captureId)) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_TRANSACTION_ID);
        }

        if (empty($currencyCode) || !in_array($currencyCode, array_keys(PayPalCodeConfiguration::getCurrencyCodes()))) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_CURRENCY);
        }

        if (empty($amount) || !Validate::isPrice($amount) || $amount <= 0) {
            throw new PayPalRefundException('', PayPalRefundException::INVALID_AMOUNT);
        }

        $this->payPalOrderId = $payPalOrderId;
        $this->captureId = $captureId;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getPayPalOrderId(): string
    {
        return $this->payPalOrderId;
    }

    /**
     * @return string
     */
    public function getCaptureId(): string
    {
        return $this->captureId;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }
}
