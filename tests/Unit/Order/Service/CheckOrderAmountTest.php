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
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;

class CheckOrderAmountTest extends TestCase
{
    /**
     * @dataProvider orderAmountProvider
     */
    public function testCheckAmount($totalAmount, $totalAmountPaid, $expectedResult)
    {
        $checkOrderAmount = new CheckOrderAmount();
        $result = $checkOrderAmount->checkAmount($totalAmount, $totalAmountPaid);
        $this->assertEquals($expectedResult, $result);
    }

    public function orderAmountProvider()
    {
        return [
            [
                '10.0000',
                '10.0000',
                CheckOrderAmount::ORDER_FULL_PAID,
            ],
            [
                '15.0000',
                '10.0000',
                CheckOrderAmount::ORDER_NOT_FULL_PAID,
            ],
            [
                '10.0000',
                '15.0000',
                CheckOrderAmount::ORDER_TO_MUCH_PAID,
            ],
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValueThrowsException($totalAmount, $totalAmountPaid, $expectedException)
    {
        $this->expectException($expectedException['exception_class']);
        $this->expectExceptionCode($expectedException['exception_code']);
        $this->expectExceptionMessage($expectedException['exception_message']);
        $checkOrderAmount = new CheckOrderAmount();
        $result = $checkOrderAmount->checkAmount($totalAmount, $totalAmountPaid);
    }

    public function invalidValueProvider()
    {
        return [
            [
                2,
                '10.0000',
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmount (integer) is not string',
                ],
            ],
            [
                [],
                '10.0000',
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmount (array) is not string',
                ],
            ],
            [
                'failed',
                '10.0000',
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmount (failed) is not numeric',
                ],
            ],
            [
                '12',
                1,
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmountPaid (integer) is not string',
                ],
            ],
            [
                '12',
                [],
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmountPaid (array) is not string',
                ],
            ],
            [
                '10',
                'Hello',
                [
                    'exception_class' => OrderException::class,
                    'exception_code' => OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER,
                    'exception_message' => 'Type of totalAmountPaid (Hello) is not numeric',
                ],
            ],
        ];
    }
}
