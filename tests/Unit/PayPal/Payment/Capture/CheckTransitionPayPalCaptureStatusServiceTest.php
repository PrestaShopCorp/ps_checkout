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

namespace Tests\Unit\PayPal\Payment\Capture;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\CheckTransitionPayPalCaptureStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;

class CheckTransitionPayPalCaptureStatusServiceTest extends TestCase
{
    /**
     * @dataProvider StatusProvider
     */
    public function testCheckAvailableStatus($oldStatus, $newStatus, $expectedResult)
    {
        $checkTransition = new CheckTransitionPayPalCaptureStatusService();
        $this->assertEquals($expectedResult, $checkTransition->checkAvailableStatus($oldStatus, $newStatus), sprintf('Transition from %s to %s should be %s', $oldStatus, $newStatus, $expectedResult ? 'allowed' : 'not allowed'));
    }

    public function statusProvider()
    {
        return [
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::PARTIALLY_REFUNDED, false],
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::REFUND, false],
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::FAILED, true],
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::DECLINED, true],
            [PayPalCaptureStatus::PENDING, PayPalCaptureStatus::COMPLETED, true],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::PARTIALLY_REFUNDED, false],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::REFUND, true],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::FAILED, false],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::DECLINED, false],
            [PayPalCaptureStatus::PARTIALLY_REFUNDED, PayPalCaptureStatus::COMPLETED, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::PARTIALLY_REFUNDED, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::REFUND, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::FAILED, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::DECLINED, false],
            [PayPalCaptureStatus::REFUND, PayPalCaptureStatus::COMPLETED, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::PARTIALLY_REFUNDED, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::REFUND, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::FAILED, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::DECLINED, false],
            [PayPalCaptureStatus::FAILED, PayPalCaptureStatus::COMPLETED, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::PARTIALLY_REFUNDED, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::REFUND, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::FAILED, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::DECLINED, false],
            [PayPalCaptureStatus::DECLINED, PayPalCaptureStatus::COMPLETED, false],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::PENDING, false],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::PARTIALLY_REFUNDED, true],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::REFUND, true],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::FAILED, false],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::DECLINED, false],
            [PayPalCaptureStatus::COMPLETED, PayPalCaptureStatus::COMPLETED, false],
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
        $checkTransition = new CheckTransitionPayPalCaptureStatusService();
        $result = $checkTransition->checkAvailableStatus($oldStatus, $newStatus);
    }

    public function invalidStatusProvider()
    {
        return [
            [
                2,
                PayPalCaptureStatus::PENDING,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (integer) is not string',
                ],
            ],
            [
                [],
                PayPalCaptureStatus::PENDING,
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
                PayPalCaptureStatus::PENDING,
                1,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of newStatus (integer) is not string',
                ],
            ],
            [
                PayPalCaptureStatus::PENDING,
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
