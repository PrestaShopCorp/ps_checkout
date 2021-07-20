<?php

namespace PrestaShop\Module\PrestashopCheckout\Repository;

use Shop;

class ShopRepository
{
    /**
     * @param $shopId
     * @return false|string
     */
    public function getShopUrl($shopId)
    {
        return (new Shop($shopId))->getBaseURL();
    }

    /**
     * @param bool $active
     * @param null $idShopGroup
     * @param false $getAsList
     * @return array
     */
    public function getShops($active = true, $idShopGroup = null, $getAsList = false)
    {
        return Shop::getShops($active, (int) $idShopGroup, $getAsList);
    }
}
