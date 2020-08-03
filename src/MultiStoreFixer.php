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

class MultiStoreFixer
{
    const UNIQUE_CONFIGURATION_KEYS = [
        'PS_CHECKOUT_SHOP_UUID_V4',
        'PS_CHECKOUT_PAYPAL_ID_MERCHANT',
        'PS_PSX_FIREBASE_ID_TOKEN',
        'PS_PSX_FIREBASE_LOCAL_ID',
        'PS_PSX_FIREBASE_REFRESH_TOKEN',
    ];

    /**
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    public function run()
    {
        return \Shop::isFeatureActive() ? $this->fixMultiStore() : $this->fixSingleStore();
    }

    /**
     * In single store mode, we remove global value and save only value for default shop
     *
     * @return bool
     */
    private function fixSingleStore()
    {
        $result = true;

        foreach (self::UNIQUE_CONFIGURATION_KEYS as $key) {
            // Get current value
            $value = \Configuration::get($key);

            // Remove current global value
            \Configuration::deleteByName($key);

            // Save current value associated to default shop
            $result = $result && \Configuration::updateValue(
                $key,
                $value,
                false,
                null,
                (int) \Context::getContext()->shop->id
            );
        }

        return $result;
    }

    /**
     * In multi store mode, we need to find shop used for payment
     * Please note Configuration::get return value for all shops or for group if no value for shop is found
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     */
    private function fixMultiStore()
    {
        $result = true;
        $currentValuesByShop = [];
        $shopListUsedByOrder = $this->getShopListUsedByOrder();

        foreach (self::UNIQUE_CONFIGURATION_KEYS as $key) {
            $globalValueUsed = false;

            // Get current values for each shop
            foreach (\Shop::getShops(false, null, true) as $shopId) {
                $globalValue = \Configuration::getGlobalValue($key);
                $shopHasGlobalValue = false === empty($globalValue);
                $shopHasUniqueKey = \Configuration::hasKey($key, null, null, (int) $shopId);
                $shopValue = \Configuration::get($key, null, null, (int) $shopId);
                $shopHasValue = false === empty($shopValue);
                $isDefaultShop = (int) $shopId === (int) \Configuration::get('PS_SHOP_DEFAULT');

                if ($shopHasUniqueKey && $shopHasValue) {
                    // This shop has a unique value, we keep it
                    $currentValuesByShop[(int) $shopId][$key] = $shopValue;
                } elseif (false === $globalValueUsed && $shopHasValue && in_array($shopId, $shopListUsedByOrder)) {
                    // This shop has order placed with checkout, we take global value found (can be value of group or all shop)
                    $currentValuesByShop[(int) $shopId][$key] = $shopValue;
                    $globalValueUsed = true;
                } elseif (false === $globalValueUsed && $shopHasValue && $isDefaultShop) {
                    // This shop is default shop, we take global value found (can be value of group or all shop)
                    $currentValuesByShop[(int) $shopId][$key] = $shopValue;
                    $globalValueUsed = true;
                } elseif (false === $globalValueUsed && $shopHasGlobalValue && $isDefaultShop) {
                    // This shop is default shop, we take global value found (value for all shop only)
                    $currentValuesByShop[(int) $shopId][$key] = $globalValue;
                    $globalValueUsed = true;
                } elseif ($key === 'PS_CHECKOUT_SHOP_UUID_V4') {
                    // No value found for ShopUuid, we generate a new one
                    (new ShopUuidManager())->generateForShop((int) $shopId);

                    // Get value previously generated and updated in Configuration
                    $currentValuesByShop[(int) $shopId][$key] = \Configuration::get(
                        $key,
                        null,
                        null,
                        (int) $shopId
                    );
                } else {
                    // No value found for this shop, reset it
                    $currentValuesByShop[(int) $shopId][$key] = '';
                }
            }
        }

        foreach (self::UNIQUE_CONFIGURATION_KEYS as $key) {
            // Remove values
            \Configuration::deleteByName($key);

            // Save values for each shop
            foreach (\Shop::getShops(false, null, true) as $shopId) {
                $result = $result && \Configuration::updateValue(
                    $key,
                    $currentValuesByShop[(int) $shopId][$key],
                    false,
                    null,
                    (int) $shopId
                );
            }
        }

        return $result;
    }

    /**
     * Get id_shop of shops with order placed using ps_checkout
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    private function getShopListUsedByOrder()
    {
        $results = \Db::getInstance()->executeS('
            SELECT DISTINCT o.id_shop
            FROM ' . _DB_PREFIX_ . 'pscheckout_order_matrice AS om
            INNER JOIN ' . _DB_PREFIX_ . 'orders AS o ON (om.id_order_prestashop = o.id_order)
        ');

        if (empty($results)) {
            return [];
        }

        return array_column($results, 'id_shop');
    }
}
