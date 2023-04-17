<?php

namespace PrestaShop\Module\PrestashopCheckout\Order\Query;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;

class GetOrderQuery
{
    /**
     * @var CartId
     */
    private $cartId;

    /**
     * @param int $cartId
     *
     * @throws CartException
     */
    public function __construct($cartId)
    {
        $this->cartId = new CartId($cartId);
    }

    /**
     * @return CartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }
}
