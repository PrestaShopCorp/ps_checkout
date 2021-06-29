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

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use PrestaShop\Module\PrestashopCheckout\ShopContext;

/**
 * Language adapter
 */
class LanguageAdapter
{
    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @param ShopContext $shopContext
     */
    public function __construct(ShopContext $shopContext)
    {
        $this->shopContext = $shopContext;
    }

    /**
     * Adapter for getLanguage from prestashop language class
     * Add locale key to the returned array on 1.6
     *
     * @param int $idLang id language
     *
     * @return array
     */
    public function getLanguage($idLang)
    {
        $language = \Language::getLanguage($idLang);

        if (false === $this->shopContext->isShop17()) {
            $locale = explode('-', $language['language_code']);
            $locale[1] = strtoupper($locale[1]);
            $language['locale'] = implode('_', $locale);
        } else {
            $language['locale'] = str_replace('-', '_', $language['locale']);
        }

        return $language;
    }
}
