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

namespace Tests\Unit\PayPal\Payment\Refund;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\CheckTransitionPayPalRefundStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\PayPalRefundStatus;

class CheckTransitionPayPalRefundStatusServiceTest extends TestCase
{
    /**
     * @dataProvider StatusProvider
     */
    public function testCheckAvailableStatus($oldStatus, $newStatus, $expectedResult)
    {
        $checkTransition = new CheckTransitionPayPalRefundStatusService();
        $this->assertEquals($expectedResult, $checkTransition->checkAvailableStatus($oldStatus, $newStatus), sprintf('Transition from %s to %s should be %s', $oldStatus, $newStatus, $expectedResult ? 'allowed' : 'not allowed'));
    }

    public function statusProvider()
    {
        return [
            [PayPalRefundStatus::PENDING, PayPalRefundStatus::PENDING, false],
            [PayPalRefundStatus::PENDING, PayPalRefundStatus::FAILED, true],
            [PayPalRefundStatus::PENDING, PayPalRefundStatus::CANCELLED, true],
            [PayPalRefundStatus::PENDING, PayPalRefundStatus::COMPLETED, true],
            [PayPalRefundStatus::FAILED, PayPalRefundStatus::PENDING, false],
            [PayPalRefundStatus::FAILED, PayPalRefundStatus::FAILED, false],
            [PayPalRefundStatus::FAILED, PayPalRefundStatus::CANCELLED, false],
            [PayPalRefundStatus::FAILED, PayPalRefundStatus::COMPLETED, false],
            [PayPalRefundStatus::CANCELLED, PayPalRefundStatus::PENDING, false],
            [PayPalRefundStatus::CANCELLED, PayPalRefundStatus::FAILED, false],
            [PayPalRefundStatus::CANCELLED, PayPalRefundStatus::CANCELLED, false],
            [PayPalRefundStatus::CANCELLED, PayPalRefundStatus::COMPLETED, false],
            [PayPalRefundStatus::COMPLETED, PayPalRefundStatus::PENDING, false],
            [PayPalRefundStatus::COMPLETED, PayPalRefundStatus::FAILED, false],
            [PayPalRefundStatus::COMPLETED, PayPalRefundStatus::CANCELLED, false],
            [PayPalRefundStatus::COMPLETED, PayPalRefundStatus::COMPLETED, false],
        ];
    }

    /**
     * @dataProvider invalidStatusProvider
     */
    public function testInvalidValueThrowsException($oldStatus, $newStatus, $expectedException)
    {
        $this->expectException($expectedException['exception_class']);
        $this->expectExceptionCode($expectedException['exception_code']);
        $this->expectExceptionMessage($expectedException['exception_message']);
        $checkTransition = new CheckTransitionPayPalRefundStatusService();
        $result = $checkTransition->checkAvailableStatus($oldStatus, $newStatus);
    }

    public function invalidStatusProvider()
    {
        return [
            [
                2,
                PayPalRefundStatus::PENDING,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (integer) is not string',
                ],
            ],
            [
                [],
                PayPalRefundStatus::PENDING,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (array) is not string',
                ],
            ],
            [
                'azeazeae',
                '10.0000',
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'The oldStatus doesn\'t exist (azeazeae)',
                ],
            ],
            [
                PayPalRefundStatus::PENDING,
                1,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of newStatus (integer) is not string',
                ],
            ],
            [
                PayPalRefundStatus::PENDING,
                [],
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of newStatus (array) is not string',
                ],
            ],
        ];
    }
}
