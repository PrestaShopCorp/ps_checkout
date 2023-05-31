<?php

namespace Tests\Unit\Cart\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use stdClass;

class CartIdTest extends TestCase
{
    public function testValidValueDoesNotThrowException()
    {
        $cartId = new CartId(1);
        $this->assertEquals(1, $cartId->getValue());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValueThrowsException($value)
    {
        $this->expectException(CartException::class);
        $this->expectExceptionCode(CartException::INVALID_ID);
        $this->expectExceptionMessage(sprintf('Cart id %s is invalid. Cart id must be number that is greater than zero.', var_export($value, true)));
        new CartId($value);
    }

    public function invalidValueProvider()
    {
        return [
            ['string'],
            [3.14],
            [[]],
            [false],
            [new stdClass()],
            [-1],
            [0],
        ];
    }
}
