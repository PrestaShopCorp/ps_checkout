<?php

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
