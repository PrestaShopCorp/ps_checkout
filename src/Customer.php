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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;

class Customer
{
    /**
     * @var PrestaShopContext
     */
    private $psContext;

    public function __construct(PrestaShopContext $psContext)
    {
        $this->psContext = $psContext;
    }

    /**
     * Get customer country by shop language and currency
     *
     * @param string $lang ISO 639-1 code
     * @param string $currency ISO 4217 code
     *
     * @return bool
     */
    public function isLang($lang, $currency)
    {
        if (
            !$lang ||
            strtolower($lang) !== strtolower($this->psContext->getLanguageIsoCode()) ||
            !$currency ||
            strtolower($currency) !== strtolower($this->psContext->getCurrencyIsoCode())
        ) {
            return false;
        }

        return true;
    }
}
