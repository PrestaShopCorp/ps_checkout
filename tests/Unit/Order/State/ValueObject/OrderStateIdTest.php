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

namespace Tests\Unit\Order\State\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateId;

class OrderStateIdTest extends TestCase
{
    public function testValidValueDoesNotThrowException()
    {
        $orderStateId = new OrderStateId(1);
        $this->assertEquals(1, $orderStateId->getValue());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValueThrowsException($value)
    {
        $this->expectException(OrderStateException::class);
        $this->expectExceptionCode(OrderStateException::INVALID_ID);
        $this->expectExceptionMessage(sprintf('Order state id %s is invalid. Order state id must be number that is greater than zero.', var_export($value, true)));
        new OrderStateId($value);
    }

    public function invalidValueProvider()
    {
        return [
            ['string'],
            [3.14],
            [[]],
            [false],
            [new \stdClass()],
            [-1],
            [0],
        ];
    }
}
