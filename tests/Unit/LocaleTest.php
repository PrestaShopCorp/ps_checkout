<?php

namespace PrestaShop\Module\PrestashopCheckout;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\LocaleException;

class LocaleTest extends TestCase
{
    /**
     * @dataProvider invalidLocaleProvider
     *
     * @throws LocaleException
     */
    public function testConstructInvalid($code, $exception)
    {
        $this->expectException($exception['class']);
        $this->expectExceptionCode($exception['code']);
        $this->expectExceptionMessage($exception['message']);
        new Locale($code);
    }

    public function invalidLocaleProvider()
    {
        return [
            [
                'EUR',
                [
                    'class' => LocaleException::class,
                    'code' => LocaleException::INVALID_CODE,
                    'message' => 'Invalid code',
                ],
            ],
            [
                12,
                [
                    'class' => LocaleException::class,
                    'code' => LocaleException::WRONG_TYPE_CODE,
                    'message' => 'CODE is not a string (integer)',
                ],
            ],
            [
                '12',
                [
                    'class' => LocaleException::class,
                    'code' => LocaleException::INVALID_CODE,
                    'message' => 'Invalid code',
                ],
            ],
        ];
    }
}
