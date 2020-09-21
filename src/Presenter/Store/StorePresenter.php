<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Store;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ConfigurationModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ContextModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\FirebaseModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PaypalModule;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\PsxModule;

/**
 * Present the store to the vuejs app (vuex)
 */
class StorePresenter implements PresenterInterface
{
    /**
     * @var \Module
     */
    private $module;

    /**
     * @var array
     */
    private $store;

    public function __construct(\Module $module, array $store = null)
    {
        // Allow to set a custom store for tests purpose
        if (null !== $store) {
            $this->store = $store;
        }

        $this->module = $module;
    }

    /**
     * Build the store required by vuex
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function present()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        /** @var ContextModule $contextModule */
        $contextModule = $this->module->getService('ps_checkout.store.module.context');
        /** @var FirebaseModule $firebaseModule */
        $firebaseModule = $this->module->getService('ps_checkout.store.module.firebase');
        /** @var PaypalModule $paypalModule */
        $paypalModule = $this->module->getService('ps_checkout.store.module.paypal');
        /** @var PsxModule $psxModule */
        $psxModule = $this->module->getService('ps_checkout.store.module.psx');
        /** @var ConfigurationModule $configurationModule */
        $configurationModule = $this->module->getService('ps_checkout.store.module.configuration');

        $this->store = array_merge(
            $contextModule->present(),
            $firebaseModule->present(),
            $paypalModule->present(),
            $psxModule->present(),
            $configurationModule->present()
        );

        return $this->store;
    }
}
