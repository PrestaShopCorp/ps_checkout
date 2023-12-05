<?php

namespace Tests\Unit\Locale\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Locale\Exception\LocaleException;
use PrestaShop\Module\PrestashopCheckout\Locale\ValueObject\CountryCode;

class LocaleCodeTest extends TestCase
{
    /**
     * @return void
     * @dataProvider intentProvider
     */
    public function testConstruct($locale, $exception)
    {
        $this->expectException(LocaleException::class);
        $this->expectExceptionMessage($exception['message']);
        $this->expectExceptionCode($exception['code']);
        new CountryCode($locale);
    }

    public function intentProvider()
    {
        return [
            [
                'locale' => 12,
                [
                    'message' => 'CODE is not a string (integer)',
                    'code' => LocaleException::WRONG_TYPE_CODE,
                ],
            ],
            [
                'locale' => 'toto',
                [
                    'message' => 'Invalid code (toto)',
                    'code' => LocaleException::INVALID_CODE,
                ],
            ],
            [
                'locale' => 'fr_fr',
                [
                    'message' => 'Invalid code (fr_fr)',
                    'code' => LocaleException::INVALID_CODE,
                ],
            ],
        ];
    }
}
