<?php

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use Currency;

class CurrencyAdapter
{
    public function getCurrencyInstance($id)
    {
        return Currency::getCurrencyInstance($id);
    }
}
