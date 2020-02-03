<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules;

use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Construct the configuration module
 */
class ConfigurationModule implements PresenterInterface
{
    /**
     * Present the paypal module (vuex)
     *
     * @return array
     */
    public function present()
    {
        $configurationModule = [
            'config' => [
                'paymentMethods' => $this->getPaymentMethods(),
                'captureMode' => \Configuration::get(
                    'PS_CHECKOUT_INTENT',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
                ),
                'paymentMode' => \Configuration::get(
                    'PS_CHECKOUT_MODE',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
                ),
                'cardIsEnabled' => (bool) \Configuration::get(
                    'PS_CHECKOUT_CARD_PAYMENT_ENABLED',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
                ),
                'debugLogsEnabled' => (bool) \Configuration::get(
                    'PS_CHECKOUT_DEBUG_LOGS_ENABLED',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
                ),
                'expressCheckout' => [
                    'orderPage' => (bool) \Configuration::get(
                        'PS_CHECKOUT_EC_ORDER_PAGE',
                        null,
                        null,
                        (int) \Context::getContext()->shop->id
                    ),
                    'checkoutPage' => (bool) \Configuration::get(
                        'PS_CHECKOUT_EC_CHECKOUT_PAGE',
                        null,
                        null,
                        (int) \Context::getContext()->shop->id
                    ),
                    'productPage' => (bool) \Configuration::get(
                        'PS_CHECKOUT_EC_PRODUCT_PAGE',
                        null,
                        null,
                        (int) \Context::getContext()->shop->id
                    ),
                ],
            ],
        ];

        return $configurationModule;
    }

    /**
     * Get payment methods order
     *
     * @return array payment method
     */
    private function getPaymentMethods()
    {
        $paymentMethods = \Configuration::get(
            'PS_CHECKOUT_PAYMENT_METHODS_ORDER',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        if (empty($paymentMethods)) {
            $paymentMethods = [
                ['name' => 'card'],
                ['name' => 'paypal'],
            ];
        } else {
            $paymentMethods = json_decode($paymentMethods, true);
        }

        return $paymentMethods;
    }
}
