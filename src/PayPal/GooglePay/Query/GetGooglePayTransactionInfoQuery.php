<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Query;

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;

class GetGooglePayTransactionInfoQuery
{
    /**
     * @var CartId
     */
    private $cartId;

    public function __construct(CartId $cartId)
    {
        $this->cartId = $cartId;
    }

    /**
     * @return CartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }
}
