<?php

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use Configuration;

class ConfigurationAdapter
{
    public function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        return Configuration::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    public function getGlobalValue($key, $idLang = null)
    {
        return Configuration::getGlobalValue($key, $idLang);
    }
}
