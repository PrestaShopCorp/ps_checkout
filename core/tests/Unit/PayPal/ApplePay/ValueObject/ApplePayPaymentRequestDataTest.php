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

namespace Tests\Unit\PsCheckout\Core\PayPal\ApplePay\ValueObject;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\ApplePay\ValueObject\ApplePayPaymentRequestData;
use PsCheckout\Core\PayPal\ApplePay\ValueObject\ApplePayTotalData;

class ApplePayPaymentRequestDataTest extends TestCase
{
    public function testGetCurrencyCodeReturnsExpectedCode(): void
    {
        $data = new ApplePayPaymentRequestData(['currency_code' => 'EUR']);

        $this->assertSame('EUR', $data->getCurrencyCode());
    }

    public function testGetCurrencyCodeReturnsEmptyStringWhenKeyMissing(): void
    {
        $data = new ApplePayPaymentRequestData([]);

        $this->assertSame('', $data->getCurrencyCode());
    }

    public function testGetTotalReturnsApplePayTotalDataWithExpectedValues(): void
    {
        $data = new ApplePayPaymentRequestData([
            'total' => ['label' => 'Total', 'amount' => '49.99'],
        ]);

        $total = $data->getTotal();

        $this->assertInstanceOf(ApplePayTotalData::class, $total);
        $this->assertSame('Total', $total->getLabel());
        $this->assertSame('49.99', $total->getAmount());
    }

    public function testGetTotalReturnsEmptyTotalDataWhenKeyMissing(): void
    {
        $data = new ApplePayPaymentRequestData([]);

        $total = $data->getTotal();

        $this->assertInstanceOf(ApplePayTotalData::class, $total);
        $this->assertSame('', $total->getLabel());
        $this->assertSame('', $total->getAmount());
    }

    public function testToArrayReturnsFullData(): void
    {
        $payload = [
            'currency_code' => 'USD',
            'total' => ['type' => 'final', 'label' => 'Total', 'amount' => '12.00'],
            'supports_coupon_code' => false,
        ];

        $data = new ApplePayPaymentRequestData($payload);

        $this->assertSame($payload, $data->toArray());
    }
}
