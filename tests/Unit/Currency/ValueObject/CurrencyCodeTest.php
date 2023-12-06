<?php

namespace Tests\Unit\Currency\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Currency\Currency;
use PrestaShop\Module\PrestashopCheckout\Currency\Exception\CurrencyException;

class CurrencyCodeTest extends TestCase
{
    /**
     * @return void
     * @dataProvider currencyProvider
     */
    public function testConstruct($name, $currency, $exception)
    {
        $this->expectException(CurrencyException::class);
        $this->expectExceptionMessage($exception['message']);
        $this->expectExceptionCode($exception['code']);
        $currency = new Currency($name, $currency);
    }

    public function currencyProvider()
    {
        return [
            [
                4,
                'currency' => 12,
                [
                    'message' => 'NAME is not a string (integer)',
                    'code' => CurrencyException::WRONG_TYPE_NAME,
                ],
            ],
            [
                'test',
                'currency' => 12,
                [
                    'message' => 'CODE is not a string (integer)',
                    'code' => CurrencyException::WRONG_TYPE_CODE,
                ],
            ],
            [
                'test',
                'currency' => 'toto',
                [
                    'message' => 'Invalid code (toto)',
                    'code' => CurrencyException::INVALID_CODE,
                ],
            ],
            [
                'test',
                'currency' => 'eur',
                [
                    'message' => 'Invalid code (eur)',
                    'code' => CurrencyException::INVALID_CODE,
                ],
            ],
        ];
    }
}
