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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;

/**
 * Handle request to maasland regarding the shop/merchant status
 */
class Shop extends PaymentClient
{
    /**
     * Used to notify PSL on settings update
     *
     * @return array
     */
    public function updateSettings()
    {
        $this->setRoute('/payments/shop/update_settings');

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var PrestaShopConfiguration $configuration */
        $configuration = $module->getService('ps_checkout.configuration');
        /** @var ExpressCheckoutConfiguration $ecConfiguration */
        $ecConfiguration = $module->getService('ps_checkout.express_checkout.configuration');

        return $this->post([
            'settings' => [
                'cb' => (bool) $configuration->get('PS_CHECKOUT_CARD_PAYMENT_ENABLED'),
                'express_in_product' => (bool) $ecConfiguration->isProductPageEnabled(),
                'express_in_cart' => (bool) $ecConfiguration->isOrderPageEnabled(),
                'express_in_checkout' => (bool) $ecConfiguration->isCheckoutPageEnabled(),
            ],
        ]);
    }
}
