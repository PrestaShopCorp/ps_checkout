<?php

namespace PrestaShop\Module\PrestashopCheckout;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\CountryException;

class CountryTest extends TestCase
{
    /**
     * @dataProvider invalidCountryProvider
     *
     * @throws CountryException
     */
    public function testConstructInvalid($name, $code, $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Country($name, $code);
    }

    public function invalidCountryProvider()
    {
        return [
            [
                'France',
                'EUR',
                [
                    'class' => CountryException::class,
                    'code' => CountryException::INVALID_CODE,
                    'message' => 'Invalid code',
                ],
            ],
            [
                'France',
                12,
                [
                    'class' => CountryException::class,
                    'code' => CountryException::WRONG_TYPE_CODE,
                    'message' => 'CODE is not a string (integer)',
                ],
            ],
            [
                'France',
                '12',
                [
                    'class' => CountryException::class,
                    'code' => CountryException::INVALID_CODE,
                    'message' => 'Invalid code',
                ],
            ],
            [
                3,
                'FR',
                [
                    'class' => CountryException::class,
                    'code' => CountryException::WRONG_TYPE_NAME,
                    'message' => 'NAME is not a string (integer)',
                ],
            ],
        ];
    }
}
