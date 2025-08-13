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

use Configuration as PrestaShopConfiguration;

class Configuration implements ConfigurationInterface
{
    private $context;

    public function __construct(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key)
    {
        $shopId = $this->context->getShop()->id;

        $result = PrestaShopConfiguration::get($key, null, null, $shopId);

        return $result ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getInteger(string $key): int
    {
        return (int) $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getBoolean(string $key): bool
    {
        return (bool) $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function getDeserializedRaw(string $key)
    {
        $configuration = $this->get($key);

        if (!$configuration) {
            return '';
        }

        return json_decode($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getForSpecificShop(string $key, int $shopId, $idLang = null)
    {
        if (!is_int($shopId)) {
            throw new \InvalidArgumentException(sprintf('Invalid argument for shopId: expected integer, got %s.', gettype($shopId)));
        }

        $result = PrestaShopConfiguration::get($key, $idLang, null, $shopId);

        return $result ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, $values): bool
    {
        $shopId = $this->context->getShop()->id;

        return PrestaShopConfiguration::updateValue($key, $values, false, null, $shopId);
    }

    /**
     * {@inheritdoc}
     */
    public function setForSpecificShop(string $key, $values, int $shopId): bool
    {
        if (!is_int($shopId)) {
            throw new \InvalidArgumentException(sprintf('Invalid argument for shopId: expected integer, got %s.', gettype($shopId)));
        }

        return PrestaShopConfiguration::updateValue($key, $values, false, null, $shopId);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByName(string $key): bool
    {
        return PrestaShopConfiguration::deleteByName($key);
    }
}
