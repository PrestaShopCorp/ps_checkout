<?php

namespace Tests\Unit\Amount;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Amount\Amount;
use PrestaShop\Module\PrestashopCheckout\Amount\Exception\AmountException;

class AmountTest extends TestCase
{
    /**
     * @dataProvider validAmountDataProvider
     *
     * @throws AmountException
     */
    public function testValidAmount($data)
    {
        $amount = new Amount($data['value'], $data['currencyCode']);
        $this->assertEquals($data['value'], $amount->getValue());
        $this->assertEquals($data['currencyCode'], $amount->getCurrencyCode());
    }

    /**
     * @dataProvider invalidAmountDataProvider
     *
     * @throws AmountException
     */
    public function testInvalidAmountThrowsException($data, $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Amount($data['value'], $data['currencyCode']);
    }

    public function validAmountDataProvider()
    {
        return [
            [
                [
                    'value' => '24.99',
                    'currencyCode' => 'EUR',
                ],
            ],
            [
                [
                    'value' => '25.00',
                    'currencyCode' => 'USD',
                ],
            ],
            [
                [
                    'value' => '17',
                    'currencyCode' => 'JPY',
                ],
            ],
        ];
    }

    public function invalidAmountDataProvider()
    {
        return [
            [
                [
                    'value' => 'twenty',
                    'currencyCode' => 'EUR',
                ],
                [
                    'class' => AmountException::class,
                    'code' => AmountException::INVALID_AMOUNT,
                    'message' => 'Amount value twenty is not a numeric',
                ],
            ],
            [
                [
                    'value' => '23..66',
                    'currencyCode' => 'USD',
                ],
                [
                    'class' => AmountException::class,
                    'code' => AmountException::INVALID_AMOUNT,
                    'message' => 'Amount value 23..66 is not a numeric',
                ],
            ],
            [
                [
                    'value' => '24.99',
                    'currencyCode' => 'FRA',
                ],
                [
                    'class' => AmountException::class,
                    'code' => AmountException::INVALID_CURRENCY,
                    'message' => 'Currency code FRA is not supported',
                ],
            ],
            [
                [
                    'value' => '24.99',
                    'currencyCode' => 'JPY',
                ],
                [
                    'class' => AmountException::class,
                    'code' => AmountException::UNEXPECTED_DECIMAL_AMOUNT,
                    'message' => 'Currency code JPY does not support decimal amount',
                ],
            ],
        ];
    }
}
