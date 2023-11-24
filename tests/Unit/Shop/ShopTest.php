<?php

namespace Tests\Unit\Shop;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\ShopException;
use PrestaShop\Module\PrestashopCheckout\Shop\Shop;

class ShopTest extends TestCase
{
    /**
     * @dataProvider invalidShopProvider
     *
     * @throws ShopException
     */
    public function testConstructInvalid($data, $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Shop($data['id'], $data['returnUrl'], $data['cancelUrl']);
    }

    public function invalidShopProvider()
    {
        return [
            [
                [
                    'id' => 'dfdf',
                    'returnUrl' => 'https://super.site',
                    'cancelUrl' => 'https://super.site',
                ],
                [
                    'class' => ShopException::class,
                    'code' => ShopException::WRONG_TYPE_ID,
                    'message' => 'ID is not an int (string)',
                ],
            ],
            [
                [
                    'id' => 2,
                    'returnUrl' => 12,
                    'cancelUrl' => 'https://super.site',
                ],
                [
                    'class' => ShopException::class,
                    'code' => ShopException::WRONG_TYPE_RETURN_URL,
                    'message' => 'ReturnUrl is not a string (integer)',
                ],
            ],
            [
                [
                    'id' => 2,
                    'returnUrl' => 'https://super.site',
                    'cancelUrl' => 14,
                ],
                [
                    'class' => ShopException::class,
                    'code' => ShopException::WRONG_TYPE_CANCEL_URL,
                    'message' => 'CancelUrl is not a string (integer)',
                ],
            ],
            [
                [
                    'id' => 2,
                    'returnUrl' => 'llll',
                    'cancelUrl' => '14',
                ],
                [
                    'class' => ShopException::class,
                    'code' => ShopException::INVALID_RETURN_URL,
                    'message' => 'ReturnUrl is not valid url',
                ],
            ],
            [
                [
                    'id' => 2,
                    'returnUrl' => 'https://super.site',
                    'cancelUrl' => '14',
                ],
                [
                    'class' => ShopException::class,
                    'code' => ShopException::INVALID_CANCEL_URL,
                    'message' => 'CancelUrl is not valid url',
                ],
            ],
        ];
    }
}
