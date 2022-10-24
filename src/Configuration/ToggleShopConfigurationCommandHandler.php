<?php

namespace PrestaShop\Module\PrestashopCheckout\Configuration;

use Db;

class ToggleShopConfigurationCommandHandler
{
    /**
     * @var Db
     */
    private $db;

    public function __construct()
    {
        $this->db = Db::getInstance();
    }

    /**
     * @param ToggleShopConfigurationCommand $command
     */
    public function handle(ToggleShopConfigurationCommand $command)
    {
        // Due to static cache in Shop::isFeatureActive(), we have to execute this to retrieve an accurate value
        $isMultiShopEnabled = (bool) $this->db->getValue('SELECT `value` FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` = "PS_MULTISHOP_FEATURE_ACTIVE"')
            && $this->db->getValue('SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'shop`') > 1;

        if ($command->getMultiShopFeatureIsCurrentlyEnabled() === $isMultiShopEnabled) {
            // When equals it means we have nothing to do
            return;
        }

        if ($isMultiShopEnabled) {
            $this->db->query(
                'UPDATE `' . _DB_PREFIX_ . 'configuration`
                SET `id_shop` = ' . (int) $command->getDefaultShopId() . '
                WHERE `name` LIKE "PS_CHECKOUT_%"
                AND `name` NOT LIKE "PS_CHECKOUT_STATE_%"
                AND `name` NOT LIKE "PS_CHECKOUT_LOGGER_%"
                AND id_shop IS NULL'
            );
        } else {
            $this->db->query(
                'UPDATE `' . _DB_PREFIX_ . 'configuration`
                SET `id_shop` = NULL, `id_shop_group` = NULL
                WHERE `name` LIKE "PS_CHECKOUT_%"
                AND `name` NOT LIKE "PS_CHECKOUT_STATE_%"
                AND `name` NOT LIKE "PS_CHECKOUT_LOGGER_%"
                AND id_shop = ' . (int) $command->getDefaultShopId()
            );
        }
    }
}
