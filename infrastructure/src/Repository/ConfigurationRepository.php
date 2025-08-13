<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PsCheckout\Infrastructure\Repository;

use Db;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Shop;

class ConfigurationRepository implements ConfigurationRepositoryInterface
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(
        Db $db,
        ConfigurationInterface $configuration
    ) {
        $this->db = $db;
        $this->configuration = $configuration;
    }

    /** {@inheritdoc} */
    public function handleConfigurationOnShopToggle()
    {
        // This is the same query as in Shop::isFeatureActive()
        // Due to static cache in Shop::isFeatureActive(), we have to execute this to retrieve an accurate value
        $isMultiShopEnabled = (bool) $this->db->getValue('SELECT value FROM ' . _DB_PREFIX_ . 'configuration WHERE name = "PS_MULTISHOP_FEATURE_ACTIVE"')
            && $this->db->getValue('SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'shop') > 1;

        // When values are equal, it means there is nothing to do
        // because the multistore value in the shop has not changed and configuration values are correct
        if (Shop::isFeatureActive() === $isMultiShopEnabled) {
            return;
        }

        $defaultShop = new Shop($this->configuration->getInteger('PS_SHOP_DEFAULT'));

        $shopIdCondition = $isMultiShopEnabled ? (int) $defaultShop->id : 'NULL';
        $shopGroupIdCondition = $isMultiShopEnabled ? (int) $defaultShop->id_shop_group : 'NULL';

        $this->db->execute(
            'UPDATE ' . _DB_PREFIX_ . 'configuration
            SET id_shop = ' . $shopIdCondition . ', id_shop_group = ' . $shopGroupIdCondition . '
            WHERE name LIKE "PS_CHECKOUT_%"
            AND name NOT LIKE "PS_CHECKOUT_STATE_%"
            AND name NOT LIKE "PS_CHECKOUT_LOGGER_%"
            AND id_shop ' . ($isMultiShopEnabled ? 'IS NULL' : '= ' . (int) $defaultShop->id)
        );
    }
}
