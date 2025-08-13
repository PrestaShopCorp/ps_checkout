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

namespace PsCheckout\Infrastructure\Adapter;

interface ConfigurationInterface
{
    /**
     * Get value from configuration.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function get(string $key);

    /**
     * Get integer value from configuration.
     *
     * @param string $key
     *
     * @return int
     */
    public function getInteger(string $key): int;

    /**
     * Get boolean value from configuration.
     *
     * @param string $key
     *
     * @return bool
     */
    public function getBoolean(string $key): bool;

    /**
     * Get deserialized raw value from configuration.
     *
     * @param string $key
     *
     * @return array|null
     */
    public function getDeserializedRaw(string $key);

    /**
     * Get value from specific shop from configuration.
     *
     * @param string $key
     * @param int $shopId
     * @param int|null $idLang
     *
     * @return string|null
     */
    public function getForSpecificShop(string $key, int $shopId, $idLang = null);

    /**
     * Update configuration key and value into database (automatically insert if key does not exist).
     *
     * Values are inserted/updated directly using SQL, because using (Configuration) ObjectModel
     * may not insert values correctly (for example, HTML is escaped, when it should not be).
     *
     * @param string $key Configuration key
     * @param mixed $values $values is an array if the configuration is multilingual, a single string else
     *
     * @return bool Update result
     */
    public function set(string $key, $values): bool;

    /**
     * Set value from specific shop from configuration.
     *
     * @param string $key Configuration key
     * @param mixed $values $values is an array if the configuration is multilingual, a single string else
     * @param int $shopId
     *
     * @return bool Update result
     */
    public function setForSpecificShop(string $key, $values, int $shopId): bool;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function deleteByName(string $key): bool;
}
