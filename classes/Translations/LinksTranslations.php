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

namespace PrestaShop\Module\PrestashopCheckout\Translations;

class LinksTranslations
{
    const PRESTASHOP_DOTCOM_URL = 'https://www.prestashop.com';

    /**
     * Locale
     *
     * @var string
     */
    private $locale;

    /**
     * __construct
     *
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->setLocale($locale);
    }

    /**
     * Get the Data Policy Link
     *
     * @return string
     */
    public function getCheckoutDataPolicyLink()
    {
        if ('fr-FR' === $this->getLocale()) {
            return self::PRESTASHOP_DOTCOM_URL . '/fr/politique-protection-donnees-prestashop-download';
        }

        return self::PRESTASHOP_DOTCOM_URL . '/en/personal-data-protection-policy-prestashop-download';
    }

    /**
     * Get the CGU Link
     *
     * @return string
     */
    public function getCheckoutCguLink()
    {
        if ('fr-FR' === $this->getLocale()) {
            return self::PRESTASHOP_DOTCOM_URL . '/fr/conditions-utilisation-prestashop-download';
        }

        return self::PRESTASHOP_DOTCOM_URL . '/en/terms-conditions-use-prestashop-download';
    }

    /**
     * getLocale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * setLocale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
}
