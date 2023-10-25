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

namespace PrestaShop\Module\PrestashopCheckout\Routing;

use Configuration;
use Context;
use Shop;

class Router
{
    /**
     * @var Context
     */
    public $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }

    /**
     * @return string
     */
    public function getCheckoutValidateLink()
    {
        return $this->context->link->getModuleLink('ps_checkout', 'validate', [], true, $this->context->language->id, $this->context->shop->id);
    }

    /**
     * @param int|null $orderId
     *
     * @return string
     */
    public function getContactLink($orderId = null)
    {
        return $this->context->link->getPageLink('contact', true, $this->context->language->id, ['id_order' => (int) $orderId]);
    }

    /**
     * @param int $idShop
     *
     * @return string
     */
    public function getDispatchWebhookLink($idShop)
    {
        return $this->context->link->getModuleLink('ps_checkout', 'DispatchWebHook', [], true, (int) Configuration::get('PS_LANG_DEFAULT'), (int) $idShop);
    }

    /**
     * @param int $idShop
     *
     * @return string
     */
    private function getBaseLink($idShop)
    {
        $shop = new Shop($idShop);
        $base = Configuration::get('PS_SSL_ENABLED') ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain;

        return $base . $shop->physical_uri;
    }
}
