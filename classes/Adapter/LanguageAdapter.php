<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Adapter;

use PrestaShop\Module\PrestashopCheckout\ShopContext;

/**
 * Language adapter
 */
class LanguageAdapter
{
    /**
     * Language object
     *
     * @var \Language
     */
    private $language;

    public function __construct(\Language $language = null)
    {
        if (null === $language) {
            $language = new \Language();
        }

        $this->language = $language;
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

        if (false === (new ShopContext())->shopIs17()) {
            $locale = explode('-', $language['language_code']);
            $locale[1] = strtoupper($locale[1]);
            $language['locale'] = implode('-', $locale);
        }

        return $language;
    }
}
