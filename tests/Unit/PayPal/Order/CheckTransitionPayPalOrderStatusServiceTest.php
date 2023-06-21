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
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;

class CheckTransitionPayPalOrderStatusServiceTest extends TestCase
{
    /**
     * @dataProvider StatusProvider
     */
    public function testCheckAvailableStatus($oldStatus, $newStatus, $expectedResult)
    {
        $checkTransition = new CheckTransitionPayPalOrderStatusService();
        $this->assertEquals($expectedResult, $checkTransition->checkAvailableStatus($oldStatus, $newStatus), sprintf('Transition from %s to %s should be %s', $oldStatus, $newStatus, $expectedResult ? 'allowed' : 'not allowed'));
    }

    public function statusProvider()
    {
        return [
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::SAVED, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::APPROVED, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::PENDING_APPROVAL, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::VOIDED, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::COMPLETED, true],
            [PayPalOrderStatus::CREATED, PayPalOrderStatus::CANCELED, true],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::APPROVED, false],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::PENDING_APPROVAL, false],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, false],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::VOIDED, true],
            [PayPalOrderStatus::SAVED, PayPalOrderStatus::COMPLETED, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::APPROVED, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::PENDING_APPROVAL, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, true],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::VOIDED, false],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::COMPLETED, true],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::CANCELED, true],
            [PayPalOrderStatus::APPROVED, PayPalOrderStatus::REVERSED, true],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::APPROVED, true],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::PENDING_APPROVAL, false],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::PAYER_ACTION_REQUIRED, true],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::VOIDED, false],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::COMPLETED, false],
            [PayPalOrderStatus::PENDING_APPROVAL, PayPalOrderStatus::CANCELED, true],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::APPROVED, true],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::PENDING_APPROVAL, true],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, false],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::VOIDED, false],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::COMPLETED, true],
            [PayPalOrderStatus::PAYER_ACTION_REQUIRED, PayPalOrderStatus::CANCELED, true],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::APPROVED, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::PENDING_APPROVAL, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::VOIDED, false],
            [PayPalOrderStatus::VOIDED, PayPalOrderStatus::COMPLETED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::CREATED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::SAVED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::APPROVED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::PENDING_APPROVAL, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::PAYER_ACTION_REQUIRED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::VOIDED, false],
            [PayPalOrderStatus::COMPLETED, PayPalOrderStatus::COMPLETED, false],
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
        $checkTransition = new CheckTransitionPayPalOrderStatusService();
        $checkTransition->checkAvailableStatus($oldStatus, $newStatus);
    }

    public function invalidStatusProvider()
    {
        return [
            [
                2,
                PayPalOrderStatus::CREATED,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (integer) is not string',
                ],
            ],
            [
                [],
                PayPalOrderStatus::CREATED,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of oldStatus (array) is not string',
                ],
            ],
            [
                'failed',
                PayPalOrderStatus::CREATED,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'The oldStatus doesn\'t exist (failed)',
                ],
            ],
            [
                PayPalOrderStatus::CREATED,
                1,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER,
                    'exception_message' => 'Type of newStatus (integer) is not string',
                ],
            ],
            [
                PayPalOrderStatus::CREATED,
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
