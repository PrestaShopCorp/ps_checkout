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

namespace PsCheckout\Tests\Unit\Order\Validator;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\Order\Validator\OrderAmountValidator;

class OrderAmountValidatorTest extends TestCase
{
    /** @var OrderAmountValidator */
    private $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new OrderAmountValidator();
    }

    /**
     * @dataProvider provideValidAmounts
     */
    public function testItValidatesAmountsSuccessfully(
        string $totalAmount,
        string $totalAmountPaid,
        int $expectedResult
    ): void {
        $result = $this->validator->validate($totalAmount, $totalAmountPaid);
        $this->assertEquals($expectedResult, $result);
    }

    public function provideValidAmounts(): array
    {
        return [
            'exact_match_integer' => [
                'totalAmount' => '100',
                'totalAmountPaid' => '100',
                'expectedResult' => OrderAmountValidator::ORDER_FULL_PAID,
            ],
            'exact_match_decimal' => [
                'totalAmount' => '100.50',
                'totalAmountPaid' => '100.50',
                'expectedResult' => OrderAmountValidator::ORDER_FULL_PAID,
            ],
            'not_full_paid_integer' => [
                'totalAmount' => '100',
                'totalAmountPaid' => '50',
                'expectedResult' => OrderAmountValidator::ORDER_NOT_FULL_PAID,
            ],
            'not_full_paid_decimal' => [
                'totalAmount' => '100.50',
                'totalAmountPaid' => '50.25',
                'expectedResult' => OrderAmountValidator::ORDER_NOT_FULL_PAID,
            ],
            'over_paid_integer' => [
                'totalAmount' => '100',
                'totalAmountPaid' => '150',
                'expectedResult' => OrderAmountValidator::ORDER_TO_MUCH_PAID,
            ],
            'over_paid_decimal' => [
                'totalAmount' => '100.50',
                'totalAmountPaid' => '150.75',
                'expectedResult' => OrderAmountValidator::ORDER_TO_MUCH_PAID,
            ],
            'small_amounts' => [
                'totalAmount' => '0.01',
                'totalAmountPaid' => '0.01',
                'expectedResult' => OrderAmountValidator::ORDER_FULL_PAID,
            ],
            'zero_amounts' => [
                'totalAmount' => '0',
                'totalAmountPaid' => '0',
                'expectedResult' => OrderAmountValidator::ORDER_FULL_PAID,
            ],
        ];
    }

    /**
     * @dataProvider provideNonNumericAmounts
     */
    public function testItThrowsExceptionForNonNumericAmounts(
        string $totalAmount,
        string $totalAmountPaid,
        string $expectedMessage
    ): void {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->expectExceptionCode(OrderException::ORDER_CHECK_AMOUNT_BAD_PARAMETER);

        $this->validator->validate($totalAmount, $totalAmountPaid);
    }

    public function provideNonNumericAmounts(): array
    {
        return [
            'total_amount_not_numeric' => [
                'totalAmount' => 'abc',
                'totalAmountPaid' => '100',
                'expectedMessage' => 'Type of totalAmount (\'abc\') is not numeric',
            ],
            'total_amount_paid_not_numeric' => [
                'totalAmount' => '100',
                'totalAmountPaid' => 'abc',
                'expectedMessage' => 'Type of totalAmountPaid (\'abc\') is not numeric',
            ],
            'both_not_numeric' => [
                'totalAmount' => 'abc',
                'totalAmountPaid' => 'def',
                'expectedMessage' => 'Type of totalAmount (\'abc\') is not numeric',
            ],
            'empty_string_total' => [
                'totalAmount' => '',
                'totalAmountPaid' => '100',
                'expectedMessage' => 'Type of totalAmount (\'\') is not numeric',
            ],
            'empty_string_paid' => [
                'totalAmount' => '100',
                'totalAmountPaid' => '',
                'expectedMessage' => 'Type of totalAmountPaid (\'\') is not numeric',
            ],
        ];
    }

    /**
     * @dataProvider provideInvalidTypeAmounts
     */
    public function testItThrowsTypeErrorForInvalidTypes(
        $totalAmount,
        $totalAmountPaid
    ): void {
        $this->expectException(\TypeError::class);

        $this->validator->validate($totalAmount, $totalAmountPaid);
    }

    public function provideInvalidTypeAmounts(): array
    {
        return [
            'null_total_amount' => [
                'totalAmount' => null,
                'totalAmountPaid' => '100',
            ],
            'array_total_amount' => [
                'totalAmount' => ['100'],
                'totalAmountPaid' => '100',
            ],
            'null_amount_paid' => [
                'totalAmount' => '100',
                'totalAmountPaid' => null,
            ],
            'array_amount_paid' => [
                'totalAmount' => '100',
                'totalAmountPaid' => ['100'],
            ],
        ];
    }
}
