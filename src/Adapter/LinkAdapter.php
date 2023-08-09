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
 * Link adapter
 */
class LinkAdapter
{
    /**
     * Link object
     *
     * @var \Link
     */
    private $link;

    public function __construct(\Link $link = null)
    {
        if (null === $link) {
            $link = new \Link();
        }

        $this->link = $link;
    }

    /**
     * Adapter for getAdminLink from prestashop link class
     *
     * @param string $controller controller name
     * @param bool $withToken include or not the token in the url
     * @param array $sfRouteParams
     * @param array $params
     *
     * @return string
     *
     * @throws \PrestaShopException
     */
    public function getAdminLink($controller, $withToken = true, $sfRouteParams = [], $params = [])
    {
        $shop = \Context::getContext()->shop;

        if ((new ShopContext())->isShop17()) {
            $adminLink = $this->link->getAdminLink($controller, $withToken, $sfRouteParams, $params);

            if ($shop->virtual_uri !== '') {
                $adminLink = str_replace($shop->physical_uri . $shop->virtual_uri, $shop->physical_uri, $adminLink);
            }

            // We have problems with links in our zoid application, since some links generated don't have domain they redirect to CDN domain
            // Routes that use new symfony router are returned without the domain
            if (strpos($adminLink, 'http') !== 0) {
                return \Tools::getShopDomainSsl(true) . $adminLink;
            }

            return $adminLink;
        }

        $paramsAsString = '';
        foreach ($params as $key => $value) {
            $paramsAsString .= "&$key=$value";
        }

        $link = \Tools::getShopDomainSsl(true) . __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_) . '/' . $this->link->getAdminLink($controller, $withToken) . $paramsAsString;

        return $shop->virtual_uri !== '' ? str_replace($shop->physical_uri . $shop->virtual_uri, $shop->physical_uri, $link) : $link;
    }
}
