<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace Tests\Unit\PayPal\Order\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use stdClass;

class PayPalOrderIdTest extends TestCase
{
    /**
     * @dataProvider validValueProvider
     */
    public function testValidValueDoesNotThrowException($value)
    {
        $paypalOrderId = new PayPalOrderId($value);
        $this->assertEquals($value, $paypalOrderId->getValue());
    }

    public function validValueProvider()
    {
        return [
            ['STRING'],
            ['1234'],
            ['5O190127TN364715T'],
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValueThrowsException($value)
    {
        $this->expectException(PayPalOrderException::class);
        $this->expectExceptionCode(PayPalOrderException::INVALID_ID);
        $this->expectExceptionMessage(sprintf('PayPal Order id %s is invalid. PayPal Order id must be an alphanumeric string.', var_export($value, true)));
        new PayPalOrderId($value);
    }

    public function invalidValueProvider()
    {
        return [
            [3.14],
            ['test'],
            [[]],
            [false],
            [new stdClass()],
            [-1],
            [0],
        ];
    }
}
