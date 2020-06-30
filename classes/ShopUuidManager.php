<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout;

use Ramsey\Uuid\Uuid;

/**
 * Manage ShopUuid
 */
class ShopUuidManager
{
    /**
     * @param int $idShop
     *
     * @return string
     */
    public function getForShop($idShop)
    {
        return \Configuration::get(
            'PS_CHECKOUT_SHOP_UUID_V4',
            null,
            null,
            (int) $idShop
        );
    }

    /**
     * Used in hook ActionObjectShopAddAfter
     *
     * @param int $idShop
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function generateForShop($idShop)
    {
        if (true === \Configuration::hasKey('PS_CHECKOUT_SHOP_UUID_V4', null, null, (int) $idShop) || $this->getForShop($idShop)) {
            return true;
        }

        if (true === \Configuration::hasKey('PSX_UUID_V4', null, null, (int) $idShop)) {
            $uuid4 = \Configuration::get(
                'PSX_UUID_V4',
                null,
                null,
                (int) $idShop
            );

            return \Configuration::updateValue(
                'PS_CHECKOUT_SHOP_UUID_V4',
                $uuid4,
                false,
                null,
                (int) $idShop
            );
        }

        return true;
    }

    /**
     * Used in module installation
     *
     * @return bool
     */
    public function generateForAllShops()
    {
        $result = true;

        foreach (\Shop::getShops(false, null, true) as $shopId) {
            $result = $result && $this->generateForShop($shopId);
        }

        return $result;
    }
}
