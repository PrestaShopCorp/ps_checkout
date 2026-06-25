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
     * @var array<string, mixed>
     */
    private $data;

    /**
     * @param array<string, mixed> $data Full assembled Apple Pay payment request fields.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        /** @var string $currencyCode */
        $currencyCode = $this->data['currency_code'] ?? '';

        return $currencyCode;
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
        $total = isset($this->data['total']) && is_array($this->data['total']) ? $this->data['total'] : [];

        /** @var string $label */
        $label = $total['label'] ?? '';
        /** @var string $amount */
        $amount = $total['amount'] ?? '';

        return new ApplePayTotalData($label, $amount);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
