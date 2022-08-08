<?php

namespace PrestaShop\Module\PrestashopCheckout\Order;

use InvalidArgumentException;


class CheckoutOrderId
{
    /**
     * @var int
     */
    private $orderId;

    /**
     * @param int $orderId
     *
     * @throws OrderException
     */
    public function __construct($orderId)
    {
        if (!is_string($orderId)) {
            throw new InvalidArgumentException(
                sprintf('Invalid type - expected string, but got (%s) "%s"', gettype($number), print_r($number, true))
            );
        }
        if (!is_numeric($orderId)) {
            throw new InvalidArgumentException('Invalid type - expected numeric, but got (%s) "%s');
        }

        $this->assertIntegerIsGreaterThanZero((int)$orderId);

        $this->orderId = (int)$orderId;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     * @throws OrderException
     */
    private function assertIntegerIsGreaterThanZero($orderId)
    {
        if (!is_int($orderId) || 0 > $orderId) {
            throw new OrderException('Order id must be greater than zero.');
        }
    }
}
