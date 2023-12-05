<?php

namespace Tests\Unit\Intent\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;
use PrestaShop\Module\PrestashopCheckout\Intent\ValueObject\Intent;

class IntentTest extends TestCase
{
    /**
     * @return void
     * @dataProvider intentProvider
     */
    public function testConstruct($intent, $exception)
    {
        $this->expectException(IntentException::class);
        $this->expectExceptionMessage($exception['message']);
        $this->expectExceptionCode($exception['code']);
        $intent = new Intent($intent);
    }

    public function intentProvider()
    {
        return [
            [
                'intent' => 12,
                [
                    'message' => 'INTENT is not a string (integer => 12).',
                    'code' => IntentException::WRONG_TYPE_INTENT,
                ],
            ],
            [
                'intent' => 'toto',
                [
                    'message' => 'INTENT is not valid (string => \'toto\').',
                    'code' => IntentException::INVALID_INTENT,
                ],
            ],
            [
                'intent' => 'capture',
                [
                    'message' => 'INTENT is not valid (string => \'capture\').',
                    'code' => IntentException::INVALID_INTENT,
                ],
            ],
        ];
    }
}
