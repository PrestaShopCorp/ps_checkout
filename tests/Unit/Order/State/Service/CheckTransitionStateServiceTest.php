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

namespace Tests\Unit\Order\State\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckOrderState;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckTransitionStateService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;

class CheckTransitionStateServiceTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetNewOrderState($data, $expectedResult)
    {
        $checkTransition = new CheckTransitionStateService(new CheckTransitionPayPalOrderStatusService(), new CheckOrderState(), new CheckOrderAmount());
        $result = $checkTransition->getNewOrderState($data);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProvider()
    {
        return [
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::COMPLETED,
                        'amount' => '10',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::CREATED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::COMPLETED,
                        'amount' => '5',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::CREATED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::PARTIALLY_PAID,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::PARTIALLY_REFUNDED,
                        'amount' => '5',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::COMPLETED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::PARTIALLY_REFUNDED,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::PARTIALLY_REFUNDED,
                        'amount' => '10',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::COMPLETED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::REFUNDED,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::REFUND,
                        'amount' => '5',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::COMPLETED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::PARTIALLY_REFUNDED,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::REFUND,
                        'amount' => '10',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::COMPLETED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::REFUNDED,
            ],
            [
                [
                    'cart' => ['amount' => 10],
                    'Order' => [
                        'currentOrderStatus' => OrderStateConfigurationKeys::WAITING_CAPTURE,
                        'totalAmountPaid' => '0',
                        'totalAmount' => '10',
                        'totalRefunded' => '0',
                    ],
                    'PayPalRefund' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalCapture' => [ // NULL si pas de refund dans l'order PayPal
                        'status' => PayPalCaptureStatus::PENDING,
                        'amount' => '10',
                    ],
                    'PayPalAuthorization' => [ // NULL si pas de refund dans l'order PayPal
                        null,
                    ],
                    'PayPalOrder' => [
                        'oldStatus' => PayPalOrderStatus::CREATED,
                        'newStatus' => PayPalOrderStatus::COMPLETED,
                    ],
                ],
                OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            ],
        ];
    }
}
