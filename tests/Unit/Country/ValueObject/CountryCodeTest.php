<?php

namespace Tests\Unit\Country\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Country\Country;
use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;

class CountryCodeTest extends TestCase
{
    /**
     * @return void
     * @dataProvider countryProvider
     */
    public function testConstruct($name, $country, $exception)
    {
        $this->expectException(CountryException::class);
        $this->expectExceptionMessage($exception['message']);
        $this->expectExceptionCode($exception['code']);
        $country = new Country($name, $country);
    }

    public function countryProvider()
    {
        return [
            [
                4,
                'country' => 12,
                [
                    'message' => 'NAME is not a string (integer)',
                    'code' => CountryException::WRONG_TYPE_NAME,
                ],
            ],
            [
                'test',
                'country' => 12,
                [
                    'message' => 'CODE is not a string (integer)',
                    'code' => CountryException::WRONG_TYPE_CODE,
                ],
            ],
            [
                'test',
                'country' => 'toto',
                [
                    'message' => 'Invalid code (toto)',
                    'code' => CountryException::INVALID_CODE,
                ],
            ],
            [
                'test',
                'country' => 'fr',
                [
                    'message' => 'Invalid code (fr)',
                    'code' => CountryException::INVALID_CODE,
                ],
            ],
        ];
    }
}
