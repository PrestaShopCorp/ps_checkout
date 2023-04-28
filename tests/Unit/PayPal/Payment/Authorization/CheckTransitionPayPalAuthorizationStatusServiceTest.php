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

namespace Tests\Unit\PayPal;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Authorization\CheckTransitionPayPalAuthorizationStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Authorization\PayPalAuthorizationStatus;

class CheckTransitionPayPalAuthorizationStatusServiceTest extends TestCase
{
    /**
     * @dataProvider StatusProvider
     */
    public function testCheckAvailableStatus($oldStatus, $newStatus, $expectedResult)
    {
        $checkTransition = new CheckTransitionPayPalAuthorizationStatusService();
        $result = $checkTransition->checkAvailableStatus($oldStatus, $newStatus);
        $this->assertEquals($expectedResult, $result);
    }

    public function statusProvider()
    {
        return [
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::CAPTURED, true],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::DENIED, true],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::EXPIRED, true],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, true],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::VOIDED, true],
            [PayPalAuthorizationStatus::CREATED, PayPalAuthorizationStatus::PENDING, true],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::CAPTURED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::DENIED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::EXPIRED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::VOIDED, false],
            [PayPalAuthorizationStatus::CAPTURED, PayPalAuthorizationStatus::PENDING, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::CAPTURED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::DENIED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::EXPIRED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::VOIDED, false],
            [PayPalAuthorizationStatus::DENIED, PayPalAuthorizationStatus::PENDING, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::CAPTURED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::DENIED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::EXPIRED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::VOIDED, false],
            [PayPalAuthorizationStatus::EXPIRED, PayPalAuthorizationStatus::PENDING, false],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::CAPTURED, true],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::DENIED, true],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::EXPIRED, true],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, false],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::VOIDED, true],
            [PayPalAuthorizationStatus::PARTIALLY_CAPTURED, PayPalAuthorizationStatus::PENDING, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::CAPTURED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::DENIED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::EXPIRED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::VOIDED, false],
            [PayPalAuthorizationStatus::VOIDED, PayPalAuthorizationStatus::PENDING, false],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::CREATED, false],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::CAPTURED, true],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::DENIED, true],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::EXPIRED, true],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::PARTIALLY_CAPTURED, true],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::VOIDED, true],
            [PayPalAuthorizationStatus::PENDING, PayPalAuthorizationStatus::PENDING, false],
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
        $checkTransition = new CheckTransitionPayPalAuthorizationStatusService();
        $result = $checkTransition->checkAvailableStatus($oldStatus, $newStatus);
    }

    public function invalidStatusProvider()
    {
        return [
            [
                2,
                PayPalAuthorizationStatus::PENDING,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (integer) is not string',
                ],
            ],
            [
                [],
                PayPalAuthorizationStatus::PENDING,
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
                PayPalAuthorizationStatus::PENDING,
                1,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of newStatus (integer) is not string',
                ],
            ],
            [
                PayPalAuthorizationStatus::PENDING,
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
