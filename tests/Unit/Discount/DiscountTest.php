<?php

namespace Tests\Unit\Amount;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Discount\Discount;
use PrestaShop\Module\PrestashopCheckout\Discount\Exception\DiscountException;

class DiscountTest extends TestCase
{
    /**
     * @dataProvider validDiscountDataProvider
     *
     * @throws DiscountException
     */
    public function testValidDiscount($data)
    {
        $discount = new Discount($data['name'], $data['value']);
        $this->assertEquals($data['name'], $discount->getName());
        $this->assertEquals($data['value'], $discount->getValue());
    }

    /**
     * @dataProvider invalidDiscountDataProvider
     *
     * @throws DiscountException
     */
    public function testInvalidAmountValueThrowsException($data, $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Discount($data['name'], $data['value']);
    }

    public function validDiscountDataProvider()
    {
        return [
            [
                [
                    'name' => '30EUR discount',
                    'value' => '30',
                ],
            ],
            [
                [
                    'name' => 'Black friday offer',
                    'value' => '24.99',
                ],
            ],
        ];
    }

    public function invalidDiscountDataProvider()
    {
        return [
            [
                [
                    'name' => '30EUR discount',
                    'value' => 30,
                ],
                [
                    'class' => DiscountException::class,
                    'code' => DiscountException::INVALID_VALUE,
                    'message' => 'Discount value is not supported',
                ],
            ],
            [
                [
                    'name' => '50% discount',
                    'value' => '50%',
                ],
                [
                    'class' => DiscountException::class,
                    'code' => DiscountException::INVALID_VALUE,
                    'message' => 'Discount value is not supported',
                ],
            ],
        ];
    }
}
