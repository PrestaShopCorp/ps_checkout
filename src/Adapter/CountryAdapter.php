<?php

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use Country;

class CountryAdapter
{
    public function getByIso($isoCode, $active = false)
    {
        return Country::getByIso($isoCode, $active);
    }
}
