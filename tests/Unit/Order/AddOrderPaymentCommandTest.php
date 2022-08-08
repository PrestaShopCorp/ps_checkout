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
namespace Tests\Unit\Order;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\NegativePaymentAmountException;
use PrestaShop\Module\PrestashopCheckout\Order\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\AddOrderPaymentCommand;

class AddOrderPaymentCommandTest extends TestCase
{
    public function testAmountIsNegative()
    {
        $this->expectException(NegativePaymentAmountException::class);
        $this->expectExceptionMessage('The amount should be greater than 0.');
        new AddOrderPaymentCommand('-1', date('Y-m-d'), 'Check', '-1', 2);
    }

    public function testOrderIdIsNegative()
    {
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage('Order id must be greater than zero.');
        new AddOrderPaymentCommand("-1", date('Y-m-d'), 'Check', "10", 2);
    }

}
