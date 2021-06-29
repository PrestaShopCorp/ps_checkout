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

namespace PrestaShop\Module\PrestashopCheckout\Shop;

use Context;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use Shop;

/**
 * Class responsible to provide current PrestaShop Shop data
 */
class ShopProvider
{
    /**
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getIdentifier()
    {
        /** @var Shop|null $shop */
        $shop = Context::getContext()->shop;

        if ($shop instanceof Shop) {
            return (int) Context::getContext()->shop->id;
        }

        throw new PsCheckoutException('Unable to retrieve current shop identifier.');
    }

    /**
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getGroupIdentifier()
    {
        /** @var Shop|null $shop */
        $shop = Context::getContext()->shop;

        if ($shop instanceof Shop) {
            return (int) Context::getContext()->shop->id_shop_group;
        }

        throw new PsCheckoutException('Unable to retrieve current shop group identifier.');
    }

    /**
     * Get one Shop Url
     *
     * @param int $shopId
     *
     * @return string
     */
    public function getShopUrl($shopId)
    {
        return (new \Shop($shopId))->getBaseURL();
    }

    /**
     * Get all shops Urls
     *
     * @return array
     */
    public function getShopsUrl()
    {
        $shopList = \Shop::getShops();
        $protocol = $this->getShopsProtocolInformations();
        $urlList = [];

        foreach ($shopList as $shop) {
            $urlList[] = [
                'id_shop' => $shop['id_shop'],
                'url' => $protocol['protocol'] . $shop[$protocol['domain_type']] . $shop['uri'],
            ];
        }

        return $urlList;
    }

    /**
     * getShopsProtocol
     *
     * @return array
     */
    protected function getShopsProtocolInformations()
    {
        if (true === \Tools::usingSecureMode()) {
            return [
                'domain_type' => 'domain_ssl',
                'protocol' => 'https://',
            ];
        }

        return [
            'domain_type' => 'domain',
            'protocol' => 'http://',
        ];
    }
}
