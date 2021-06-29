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

namespace PrestaShop\Module\PrestashopCheckout\Dispatcher;

use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;
use Psr\SimpleCache\CacheInterface;

class MerchantDispatcher implements Dispatcher
{
    /**
     * Dispatch the Event Type to manage the merchant status
     *
     * {@inheritdoc}
     *
     * @throws PsCheckoutException
     */
    public function dispatchEventType($payload)
    {
        $paypalAccount = new PaypalAccount($payload['merchantId']);

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var CacheInterface $merchantIntegrationCache */
        $merchantIntegrationCache = $module->getService('ps_checkout.cache.paypal.merchant_integration');

        // Webhook inform we need to retrieve fresh data
        if ($merchantIntegrationCache->has($payload['merchantId'])) {
            $merchantIntegrationCache->delete($payload['merchantId']);
        }

        // Cache used provide pruning (deletion) of all expired cache items to reduce cache size
        if (method_exists($merchantIntegrationCache, 'prune')) {
            $merchantIntegrationCache->prune();
        }

        /** @var PaypalAccountUpdater $accountUpdater */
        $accountUpdater = $module->getService('ps_checkout.updater.paypal.account');

        return $accountUpdater->update($paypalAccount);
    }
}
