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

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class responsible to define default value for PrestaShop configuration options
 */
class PrestaShopConfigurationOptionsResolver
{
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * @param int $shopId
     */
    public function __construct($shopId)
    {
        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver->setDefaults([
            'global' => false,
            'html' => false,
            'default' => false,
            'id_lang' => null,
        ]);
        $this->optionsResolver->setDefault('id_shop', function (Options $options) use ($shopId) {
            if (true === $options['global']) {
                return 0;
            }

            return $shopId;
        });
        $this->optionsResolver->setDefault('id_shop_group', function (Options $options) {
            if (true === $options['global']) {
                return 0;
            }

            return null;
        });
        $this->optionsResolver->setAllowedTypes('global', 'bool');
        $this->optionsResolver->setAllowedTypes('id_lang', ['null', 'int']);
        $this->optionsResolver->setAllowedTypes('id_shop', ['null', 'int']);
        $this->optionsResolver->setAllowedTypes('id_shop_group', ['null', 'int']);
        $this->optionsResolver->setAllowedTypes('html', 'bool');
    }

    public function resolve(array $options)
    {
        return $this->optionsResolver->resolve($options);
    }
}
