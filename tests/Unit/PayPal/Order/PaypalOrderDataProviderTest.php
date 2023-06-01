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

namespace Tests\Unit\PayPal\Order;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PaypalOrderDataProvider;

class PaypalOrderDataProviderTest extends TestCase
{
    public function testPaymentSuccessful()
    {
        $orderPayPalDataProvider = new PaypalOrderDataProvider([
            'id' => '32450127TN364715T',
            'status' => 'COMPLETED',
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            [
                                'id' => '3C679366HH908993F',
                                'status' => 'COMPLETED',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertEquals('32450127TN364715T', $orderPayPalDataProvider->getOrderId());
        $this->assertEquals('COMPLETED', $orderPayPalDataProvider->getOrderStatus());
        $this->assertEquals('3C679366HH908993F', $orderPayPalDataProvider->getTransactionId());
        $this->assertEquals('COMPLETED', $orderPayPalDataProvider->getTransactionStatus());
    }

    public function testPayerActionRequired()
    {
        $orderPayPalDataProvider = new PaypalOrderDataProvider([
            'id' => '5O190127TN364715T',
            'status' => 'PAYER_ACTION_REQUIRED',
            'links' => [
                [
                    'rel' => 'payer-action',
                    'href' => 'https://www.paypal.com/payment/alipay?token=5O190127TN364715T',
                ],
            ],
        ]);
        $this->assertEquals('5O190127TN364715T', $orderPayPalDataProvider->getOrderId());
        $this->assertEquals('PAYER_ACTION_REQUIRED', $orderPayPalDataProvider->getOrderStatus());
        $this->assertEquals('https://www.paypal.com/payment/alipay?token=5O190127TN364715T', $orderPayPalDataProvider->getPayActionLink());
    }

    public function testPendingApproval()
    {
        $orderPayPalDataProvider = new PaypalOrderDataProvider([
            'id' => '5O190127TN364715T',
            'status' => 'PENDING_APPROVAL',
            'links' => [
                [
                    'rel' => 'approve',
                    'href' => 'https://www.paypal.com/checkoutnow?token=5O190127TN364715T',
                ],
            ],
        ]);
        $this->assertEquals('5O190127TN364715T', $orderPayPalDataProvider->getOrderId());
        $this->assertEquals('PENDING_APPROVAL', $orderPayPalDataProvider->getOrderStatus());
        $this->assertEquals('https://www.paypal.com/checkoutnow?token=5O190127TN364715T', $orderPayPalDataProvider->getApprovalLink());
    }
}
