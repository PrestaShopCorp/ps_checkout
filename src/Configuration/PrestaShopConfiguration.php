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

namespace PrestaShop\Module\PrestashopCheckout\Configuration;

use Configuration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * Class responsible to manage PrestaShop configuration
 */
class PrestaShopConfiguration
{
    /**
     * @var PrestaShopConfigurationOptionsResolver
     */
    private $optionsResolver;

    /**
     * @param PrestaShopConfigurationOptionsResolver $optionsResolver
     */
    public function __construct(PrestaShopConfigurationOptionsResolver $optionsResolver)
    {
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * @param string $key
     * @param array $options Options
     *
     * @return bool
     */
    public function has($key, array $options = [])
    {
        $settings = $this->optionsResolver->resolve($options);

        return (bool) Configuration::hasKey(
            $key,
            $settings['id_lang'],
            $settings['id_shop_group'],
            $settings['id_shop']
        );
    }

    /**
     * @param string $key
     * @param array $options Options
     *
     * @return mixed
     */
    public function get($key, array $options = [])
    {
        $settings = $this->optionsResolver->resolve($options);

        $value = Configuration::get(
            $key,
            $settings['id_lang'],
            $settings['id_shop_group'],
            $settings['id_shop']
        );

        if (empty($value)) {
            return $settings['default'];
        }

        return $value;
    }

    /**
     * Set configuration value.
     *
     * @param string $key
     * @param mixed $value
     * @param array $options Options
     *
     * @return $this
     *
     * @throws PsCheckoutException
     */
    public function set($key, $value, array $options = [])
    {
        $settings = $this->optionsResolver->resolve($options);

        $success = (bool) Configuration::updateValue(
            $key,
            $value,
            $settings['html'],
            $settings['id_shop_group'],
            $settings['id_shop']
        );

        if (false === $success) {
            throw new PsCheckoutException(sprintf('Could not set key %s in PrestaShop configuration', $key));
        }

        return $this;
    }

    /**
     * @param string $key
     *
     * @return $this
     *
     * @throws PsCheckoutException
     */
    public function remove($key)
    {
        $success = (bool) Configuration::deleteByName($key);

        if (false === $success) {
            throw new PsCheckoutException(sprintf('Could not remove key %s from PrestaShop configuration', $key));
        }

        return $this;
    }
}
