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

namespace PsCheckout\Core\PayPal\ApplePay\ValueObject;

class ApplePayPaymentRequestData
{
    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @var ApplePayTotalData
     */
    private $total;

    /**
     * @param string $currencyCode
     * @param ApplePayTotalData $total
     */
    public function __construct(string $currencyCode, ApplePayTotalData $total)
    {
        $this->currencyCode = $currencyCode;
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    /**
     * This method returns "Total" data for an Apple Pay payment.
     *
     * @see https://developer.paypal.com/docs/checkout/apm/apple-pay/ Apple Pay Documentation
     *
     * @return ApplePayTotalData
     */
    public function getTotal(): ApplePayTotalData
    {
        return $this->total;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'currency_code' => $this->currencyCode,
            'total' => [
                'type' => $this->total->getType(),
                'label' => $this->total->getLabel(),
                'amount' => $this->total->getAmount(),
            ],
        ];
    }
}
